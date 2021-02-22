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
use App\Model\Repository\PohledRepository;
use App\Model\Repository\ZamestnanecRepository;
use App\Components\IPohledFormFactory;
use App\Components\IPohledControlFactory;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;

//define("ALL", -1);

class PohledPresenter extends BasePresenter
{
    /** @var PohledRepository */
    private $pohledRepository;

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /**
     * @var IPohledControlFactory
     * @inject
     */
    public $pohledControlFactory;


    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $pomerId;
    /** @persistent */
    public $zamestnanecId;

    public function __construct(PohledRepository $pohledRepository,ZamestnanecRepository $zamestnanecRepository)
    {
        parent::__construct();
        $this->pohledRepository = $pohledRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->pohledTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->zamestnanecId = NULL;
        //$this->template->pohledTypes = $this->pohledRepository->getAllPohledTypes();

    }


    function renderCreate($zamestnanecId)
    {
       // $this->template->pohledAttributes = $this->pohledRepository->getAllPohledAttributesForPohledType($pohledTypeId);
        //$this->zamestnanecId = $zamestnanecId;
        $zamestnanecName = $this->zamestnanecRepository->getZamestnanec($zamestnanecId)->billingFullName;
        $this['pohledForm']->setDefaults(['zamestnanecId'=>$zamestnanecId,'zamestnanecName' => $zamestnanecName,"created" => date('Y-m-d', time()) ]);
    }


    function renderDetail($pomerId)
    {
        $pohled = $this->pohledRepository->getPohled($pomerId);
        if($pohled){
          $this->pomerId = $pomerId;
          $this->template->pohled = $pohled;
          $pohledBasicAttributes = $pohled->toArray();
          $pohledBasicAttributes["zamestnanecName"] = $this->zamestnanecRepository->getName($pohled->zamestnanecId);
          $pohledBasicAttributes["nastup"] = date('Y-m-d',$pohledBasicAttributes["nastup"]->getTimestamp());
          if($pohledBasicAttributes["vystup"]->getTimestamp()>0)
            $pohledBasicAttributes["vystup"] = date('Y-m-d',$pohledBasicAttributes["vystup"]->getTimestamp());
          //$pohledBasicAttributes["zamestnanecName"] = $pohled->zamestnanec->billingFullName;

          $this['pohledForm']->setDefaults($pohledBasicAttributes);
        }
        else {
          $this->redirect('Pohled:');
        }

    }


    public function handleCreatePohled()
    {

        // if ($this->isAjax()) {
        //     $this->payload->isModal = TRUE;
        //     $this->redrawControl("modal");
        // }
    }

    // protected function createComponentPohledForm()
    // {
    //
    //
    //     $form = $this->pohledFormFactory->create($this->pomerId);
    //     $this->template->zamestnanecId = $this->zamestnanecId;
    //     $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
    //       if($form['submit']->isSubmittedBy()){
    //         $this->flashMessage('Objednávka byla aktualizována a změny uloženy.', 'alert-success');
    //         if($this->zamestnanecId)
    //           $this->redirect('Zamestnanec:');
    //         $this->redirect('Pohled:');
    //       }
    //       if($form['delete']->isSubmittedBy()){
    //         $this->flashMessage('Objednávka byla odstraněna.', 'alert-success');
    //
    //       }
    //
    //     };
    //
    //     return $form;
    //
    // }
    // protected function createComponentPohledLogEntryForm()
    // {
    //     $form = $this->pohledLogEntryFormFactory->create($this->pomerId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);
    //
    //     $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
    //       if($form['submit']->isSubmittedBy()){
    //         $this->flashMessage('Záznam byl přidán.', 'alert-success');
    //       //  $this->redirect('Pohled:');
    //       }
    //     };
    //     return $form;
    //
    // }




    public function createComponentPohledControl()
    {
        return $this->pohledControlFactory->create();
    }

    public function actionPrint($pomerId)
    {
      $pohled = $this->pohledRepository->getPohled($pomerId);
      $zamestnanec = $this->zamestnanecRepository->getZamestnanec($pohled->zamestnanecId);
      $template = $this->templateFactory->createTemplate();
      $template->setFile(__DIR__ . '/templates/Pohled/pdf.latte');
      $template->pohled = $pohled;
      $template->zamestnanec = $zamestnanec;
      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_objednavka_".$pomerId .".pdf", \Mpdf\Output\Destination::DOWNLOAD);
 //$pdf->Output();
      $this->redirect('Invoice:');
    }

}
