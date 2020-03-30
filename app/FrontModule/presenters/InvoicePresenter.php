<?php

/**
 *
 *
 */

namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Repository\InvoiceRepository;
use App\Forms\IInvoiceFormFactory;
use App\Forms\InvoiceForm;
use App\Components\IInvoiceControlFactory;
use App\Components\IOrderControlFactory;
use App\Model\Repository\OrderRepository;
use App\Model\Repository\CustomerRepository;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;
//define("ALL", -1);

class InvoicePresenter extends BasePresenter
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var OrderRepository */
    private $orderRepository;

    /** @var CustomerRepository */
    private $customerRepository;

    /**
     * @var IInvoiceControlFactory
     * @inject
     */
    public $invoiceControlFactory;

    /** @var \IInvoiceFormFactory */
    private $invoiceFormFactory;

    public $customerControlFactory;
    /**
     * @var IOrderControlFactory
     * @inject
     */
    public $orderControlFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $invoiceId;
    /** @persistent */
    public $customerId;

    public function __construct(InvoiceRepository $invoiceRepository,OrderRepository $orderRepository,CustomerRepository $customerRepository,
                                InvoiceForm $invoiceFormFactory)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->invoiceFormFactory =  $invoiceFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->invoiceTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->customerId = NULL;
        //$this->template->invoiceTypes = $this->invoiceRepository->getAllInvoiceTypes();

    }


    function actionCreate($customerId)
    {
        $orders = $this->orderRepository->getOrdersWithoutInvoice($customerId);
      if(count($orders)){
        $invoiceId = $this->invoiceRepository->makeInvoice($customerId);

       foreach ($orders as $order) {
          $this->flashMessage('Objednávka '.$order->orderId.' byla přidána k faktuře.', 'alert-success');
          $this->orderRepository->addInvoiceId($order->orderId, $invoiceId);
        }
          $total = $this->orderRepository->getTotalInvoice($invoiceId);
          $data['total']=$total;
          $data['created_by']=$this->user->getId();
          $invoice = $this->invoiceRepository->getInvoice($invoiceId);
          $invoice->update($data);
        $this->flashMessage('Faktura za '.$total .' kč byla vytvořena a změny uloženy.', 'alert-success');
      }
      else
        $this->flashMessage('Nebyly nalezeny fakturovatelné objednávky.', 'alert-danger');
      $this->redirect('Customer:');
       // $this->template->invoiceAttributes = $this->invoiceRepository->getAllInvoiceAttributesForInvoiceType($invoiceTypeId);
        //$this->customerId = $customerId;
        //$this['invoiceForm']->setDefaults(['fk_customer'=>$customerId]);

    }


    function renderDetail($invoiceId)
    {
        $invoice = $this->invoiceRepository->getInvoice($invoiceId);
        if($invoice){
          $invoiceBasicAttributes = $invoice->toArray();
          $invoiceBasicAttributes["customerName"] = $this->customerRepository->getName($invoice->fk_customer);
          //$invoiceBasicAttributes["customerName"] = $invoice->customer->billingFullName;

          $this['invoiceForm']->setDefaults($invoiceBasicAttributes);
        }else {
          $this->redirect("Invoice:");
        }
    }


    public function handleCreateInvoice()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    protected function createComponentInvoiceForm()
    {


        $form = $this->invoiceFormFactory->create($this->invoiceId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Faktura byla aktualizována a změny uloženy.', 'alert-success');
            if($this->customerId)
              $this->redirect('Customer:');
            $this->redirect('Invoice:');
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Faktura byla odstraněna.', 'alert-success');
          //  $this->redirect('Invoice:');
          }

        };

        return $form;

    }
    protected function createComponentInvoiceLogEntryForm()
    {
        $form = $this->invoiceLogEntryFormFactory->create($this->invoiceId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('Invoice:');
          }
        };
        return $form;

    }




    public function createComponentInvoiceControl()
    {
        return $this->invoiceControlFactory->create();
    }
    public function createComponentOrderControl()
    {
        return $this->orderControlFactory->create();
    }

    public function actionPrint($invoiceId)
    {
      $template = $this->templateFactory->createTemplate();

      $invoice = $this->invoiceRepository->getInvoice($invoiceId);
      $template->invoice = $invoice;
      $template->customer = $this->customerRepository->getCustomer($invoice->fk_customer);
      $template->orders = $this->orderRepository->getAllCustomer($invoice->fk_customer);

      $template->setFile(__DIR__ . '/templates/Invoice/pdf.latte');

      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_faktura_".$invoiceId .".pdf", \Mpdf\Output\Destination::DOWNLOAD);
//$pdf->Output();

      $this->redirect('Invoice:');
    }

}
