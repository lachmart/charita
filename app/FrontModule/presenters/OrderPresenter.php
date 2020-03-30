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
use App\Model\Repository\OrderRepository;
use App\Model\Repository\CustomerRepository;
use App\Forms\IOrderFormFactory;
use App\Forms\OrderForm;
use App\Components\IOrderControlFactory;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;

//define("ALL", -1);

class OrderPresenter extends BasePresenter
{
    /** @var OrderRepository */
    private $orderRepository;

    /** @var CustomerRepository */
    private $customerRepository;

    /**
     * @var IOrderControlFactory
     * @inject
     */
    public $orderControlFactory;

    /** @var \IOrderFormFactory */
    private $orderFormFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $orderId;
    /** @persistent */
    public $customerId;

    public function __construct(OrderRepository $orderRepository,CustomerRepository $customerRepository,
                                OrderForm $orderFormFactory)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->orderFormFactory =  $orderFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->orderTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->customerId = NULL;
        //$this->template->orderTypes = $this->orderRepository->getAllOrderTypes();

    }


    function renderCreate($customerId)
    {
       // $this->template->orderAttributes = $this->orderRepository->getAllOrderAttributesForOrderType($orderTypeId);
        //$this->customerId = $customerId;
        $customerName = $this->customerRepository->getCustomer($customerId)->billingFullName;
        $this['orderForm']->setDefaults(['fk_customer'=>$customerId,'customerName' => $customerName,"created" => date('Y-m-d', time()) ]);
    }


    function renderDetail($orderId)
    {
        $order = $this->orderRepository->getOrder($orderId);
        if($order){
          $this->orderId = $orderId;
          $this->template->order = $order;
          $orderBasicAttributes = $order->toArray();
          $orderBasicAttributes["customerName"] = $this->customerRepository->getName($order->fk_customer);
          $orderBasicAttributes["created"] = date('Y-m-d',$orderBasicAttributes["created"]->getTimestamp());
          //$orderBasicAttributes["customerName"] = $order->customer->billingFullName;

          $this['orderForm']->setDefaults($orderBasicAttributes);
        }
        else {
          $this->redirect('Order:');
        }

    }


    public function handleCreateOrder()
    {

        // if ($this->isAjax()) {
        //     $this->payload->isModal = TRUE;
        //     $this->redrawControl("modal");
        // }
    }

    protected function createComponentOrderForm()
    {


        $form = $this->orderFormFactory->create($this->orderId);
        $this->template->customerId = $this->customerId;
        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Objednávka byla aktualizována a změny uloženy.', 'alert-success');
            if($this->customerId)
              $this->redirect('Customer:');
            $this->redirect('Order:');
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Objednávka byla odstraněna.', 'alert-success');

          }

        };

        return $form;

    }
    protected function createComponentOrderLogEntryForm()
    {
        $form = $this->orderLogEntryFormFactory->create($this->orderId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('Order:');
          }
        };
        return $form;

    }




    public function createComponentOrderControl()
    {
        return $this->orderControlFactory->create();
    }

    public function actionPrint($orderId)
    {
      $order = $this->orderRepository->getOrder($orderId);
      $customer = $this->customerRepository->getCustomer($order->fk_customer);
      $template = $this->templateFactory->createTemplate();
      $template->setFile(__DIR__ . '/templates/Order/pdf.latte');
      $template->order = $order;
      $template->customer = $customer;
      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_objednavka_".$orderId .".pdf", \Mpdf\Output\Destination::DOWNLOAD);
 //$pdf->Output();
      $this->redirect('Invoice:');
    }

}
