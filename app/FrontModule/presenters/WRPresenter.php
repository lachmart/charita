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
use App\Model\Repository\WRRepository;
use App\Model\Repository\ZamestnanecRepository;
use App\Forms\IWRFormFactory;
use App\Forms\WRForm;
use App\Components\IWRControlFactory;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;
use App\Components\IInvoiceControlFactory;


//define("ALL", -1);

class WRPresenter extends BasePresenter
{
    /** @var WRRepository */
    private $wRRepository;

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;
    /**
     * @var IInvoiceControlFactory
     * @inject
     */
    public $invoiceControlFactory;
    /** @var \IWRFormFactory */
    private $wRFormFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $pomerId;
    /** @persistent */
    public $zamestnanecId;

    public function __construct(WRRepository $wRRepository,ZamestnanecRepository $zamestnanecRepository,
                                WRForm $wRFormFactory)
    {
        parent::__construct();
        $this->wRRepository = $wRRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->wRFormFactory =  $wRFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->wRTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->zamestnanecId = NULL;
        //$this->template->wRTypes = $this->wRRepository->getAllWRTypes();

    }


    function renderCreate($zamestnanecId)
    {
       // $this->template->wRAttributes = $this->wRRepository->getAllWRAttributesForWRType($wRTypeId);
        //$this->zamestnanecId = $zamestnanecId;
        $zamestnanecName = $this->zamestnanecRepository->getZamestnanec($zamestnanecId)->billingFullName;
        $this['wRForm']->setDefaults(['zamestnanecId'=>$zamestnanecId,'zamestnanecName' => $zamestnanecName,"created" => date('Y-m-d', time()) ]);
    }


    function renderDetail($pomerId)
    {
        $wR = $this->wRRepository->get($pomerId);
        if($wR){
          $this->pomerId = $pomerId;
          $this->template->wR = $wR;
          $wRBasicAttributes = $wR->toArray();
          $wRBasicAttributes["zamestnanecName"] = $this->zamestnanecRepository->getName($wR->zamestnanecId);

          $this['wRForm']->setDefaults($wRBasicAttributes);
        }
        else {
         // $this->redirect('WR:');
        }

    }


    public function handleCreateWR()
    {

        // if ($this->isAjax()) {
        //     $this->payload->isModal = TRUE;
        //     $this->redrawControl("modal");
        // }
    }

    protected function createComponentWRForm()
    {


        $form = $this->wRFormFactory->create($this->pomerId);
        $this->template->zamestnanecId = $this->zamestnanecId;
        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Objednávka byla aktualizována a změny uloženy.', 'alert-success');
            if($this->zamestnanecId)
              $this->redirect('Zamestnanec:');
            $this->redirect('WR:');
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Objednávka byla odstraněna.', 'alert-success');

          }

        };

        return $form;

    }
    protected function createComponentWRLogEntryForm()
    {
        $form = $this->wRLogEntryFormFactory->create($this->pomerId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('WR:');
          }
        };
        return $form;

    }




    public function createComponentWRControl()
    {
        return $this->wRControlFactory->create();
    }
    public function createComponentInvoiceControl()
    {
        return $this->invoiceControlFactory->create();
    }

    public function actionPrint($pomerId)
    {
      $wR = $this->wRRepository->getWR($pomerId);
      $zamestnanec = $this->zamestnanecRepository->getZamestnanec($wR->zamestnanecId);
      $template = $this->templateFactory->createTemplate();
      $template->setFile(__DIR__ . '/templates/WR/pdf.latte');
      $template->wR = $wR;
      $template->zamestnanec = $zamestnanec;
      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_objednavka_".$pomerId .".pdf", \Mpdf\Output\Destination::DOWNLOAD);
 //$pdf->Output();
      $this->redirect('Invoice:');
    }

}
