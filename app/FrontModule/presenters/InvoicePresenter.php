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
use App\Components\IWRControlFactory;
use App\Model\Repository\WRRepository;
use App\Model\Repository\ZamestnanecRepository;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;
use App\Components\IRozpadstrediskaControlFactory;
//define("ALL", -1);

class InvoicePresenter extends BasePresenter
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var WRRepository */
    private $wRRepository;

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /**
     * @var IInvoiceControlFactory
     * @inject
     */
    public $invoiceControlFactory;

    /** @var \IInvoiceFormFactory */
    private $invoiceFormFactory;

    public $zamestnanecControlFactory;
    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $mzdovyComId;
    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    /**
     * @var IRozpadstrediskaControlFactory
     * @inject
     */
    public $rozpadstrediskaControlFactory;

    public function __construct(InvoiceRepository $invoiceRepository,WRRepository $wRRepository,ZamestnanecRepository $zamestnanecRepository,
                                InvoiceForm $invoiceFormFactory)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->wRRepository = $wRRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->invoiceFormFactory =  $invoiceFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->invoiceTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->zamestnanecId = NULL;
      $this->pomerId = NULL;
        //$this->template->invoiceTypes = $this->invoiceRepository->getAllInvoiceTypes();

    }


    function actionCreate($zamestnanecId)
    {
        $wRs = $this->wRRepository->getWRsWithoutInvoice($zamestnanecId);
      if(count($wRs)){
        $mzdovyComId = $this->invoiceRepository->makeInvoice($zamestnanecId);

       foreach ($wRs as $wR) {
          $this->flashMessage('Objednávka '.$wR->pomerId.' byla přidána k faktuře.', 'alert-success');
          $this->wRRepository->addInvoiceId($wR->pomerId, $mzdovyComId);
        }
          $total = $this->wRRepository->getTotalInvoice($mzdovyComId);
          $data['total']=$total;
          $data['created_by']=$this->user->getId();
          $invoice = $this->invoiceRepository->getInvoice($mzdovyComId);
          $invoice->update($data);
        $this->flashMessage('Faktura za '.$total .' kč byla vytvořena a změny uloženy.', 'alert-success');
      }
      else
        $this->flashMessage('Nebyly nalezeny fakturovatelné objednávky.', 'alert-danger');
      $this->redirect('Zamestnanec:');
       // $this->template->invoiceAttributes = $this->invoiceRepository->getAllInvoiceAttributesForInvoiceType($invoiceTypeId);
        //$this->zamestnanecId = $zamestnanecId;
        //$this['invoiceForm']->setDefaults(['zamestnanecId'=>$zamestnanecId]);

    }


    function renderDetail($mzdovyComId)
    {
        $invoice = $this->invoiceRepository->getInvoice($mzdovyComId);
        if($invoice){
          $invoiceBasicAttributes = $invoice->toArray();
          $invoiceBasicAttributes["zamestnanecName"] = $this->zamestnanecRepository->getName($invoice->zamestnanecId);
          //$invoiceBasicAttributes["zamestnanecName"] = $invoice->zamestnanec->billingFullName;

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


        $form = $this->invoiceFormFactory->create($this->mzdovyComId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Faktura byla aktualizována a změny uloženy.', 'alert-success');
            if($this->zamestnanecId)
              $this->redirect('Zamestnanec:');
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
        $form = $this->invoiceLogEntryFormFactory->create($this->mzdovyComId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

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
    public function createComponentRozpadstrediskaControl()
    {
        return $this->rozpadstrediskaControlFactory->create();
    }

    public function actionPrint($mzdovyComId)
    {
      $template = $this->templateFactory->createTemplate();

      $invoice = $this->invoiceRepository->getInvoice($mzdovyComId);
      $template->invoice = $invoice;
      $template->zamestnanec = $this->zamestnanecRepository->getZamestnanec($invoice->zamestnanecId);
      $template->wRs = $this->wRRepository->getAllInvoice($mzdovyComId);

      $formatedId = sprintf("%03d", $invoice->zamestnanecId);
      $weekCreated = date('W',$invoice->created->getTimestamp());

      $variabSymbol = "1712".$formatedId.$weekCreated;


      $template->variabSymbol = $variabSymbol;
      $template->dateToPay = date("d. m. Y",$invoice->created->getTimestamp()+604800+604800);



      $template->setFile(__DIR__ . '/templates/Invoice/pdf.latte');



      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_faktura_".$variabSymbol.".pdf", \Mpdf\Output\Destination::DOWNLOAD);
      //$pdf->Output();

      $this->redirect('Invoice:');
    }

}
