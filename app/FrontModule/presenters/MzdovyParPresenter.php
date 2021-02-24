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
use App\Model\Repository\MzdovyParRepository;
use App\Forms\IMzdovyParFormFactory;
use App\Forms\MzdovyParForm;
use App\Components\IMzdovyParControlFactory;
use App\Components\IWRControlFactory;
use App\Model\Repository\WRRepository;
use App\Model\Repository\ZamestnanecRepository;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;
use App\Components\IRozpadstrediskaControlFactory;
//define("ALL", -1);

class MzdovyParPresenter extends BasePresenter
{
    /** @var MzdovyParRepository */
    private $mzdovyParRepository;

    /** @var WRRepository */
    private $wRRepository;

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /**
     * @var IMzdovyParControlFactory
     * @inject
     */
    public $mzdovyParControlFactory;

    /** @var \IMzdovyParFormFactory */
    private $mzdovyParFormFactory;

    public $zamestnanecControlFactory;
    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $mzdovyParId;
    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    /**
     * @var IRozpadstrediskaControlFactory
     * @inject
     */
    public $rozpadstrediskaControlFactory;

    public function __construct(MzdovyParRepository $mzdovyParRepository,WRRepository $wRRepository,ZamestnanecRepository $zamestnanecRepository,
                                MzdovyParForm $mzdovyParFormFactory)
    {
        parent::__construct();
        $this->mzdovyParRepository = $mzdovyParRepository;
        $this->wRRepository = $wRRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->mzdovyParFormFactory =  $mzdovyParFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->mzdovyParTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->zamestnanecId = NULL;
      $this->pomerId = NULL;
        //$this->template->mzdovyParTypes = $this->mzdovyParRepository->getAllMzdovyParTypes();

    }


    function actionCreate($zamestnanecId)
    {
        $wRs = $this->wRRepository->getWRsWithoutMzdovyPar($zamestnanecId);
      if(count($wRs)){
        $mzdovyParId = $this->mzdovyParRepository->makeMzdovyPar($zamestnanecId);

       foreach ($wRs as $wR) {
          $this->flashMessage('Objednávka '.$wR->pomerId.' byla přidána k faktuře.', 'alert-success');
          $this->wRRepository->addMzdovyParId($wR->pomerId, $mzdovyParId);
        }
          $total = $this->wRRepository->getTotalMzdovyPar($mzdovyParId);
          $data['total']=$total;
          $data['created_by']=$this->user->getId();
          $mzdovyPar = $this->mzdovyParRepository->getMzdovyPar($mzdovyParId);
          $mzdovyPar->update($data);
        $this->flashMessage('Faktura za '.$total .' kč byla vytvořena a změny uloženy.', 'alert-success');
      }
      else
        $this->flashMessage('Nebyly nalezeny fakturovatelné objednávky.', 'alert-danger');
      $this->redirect('Zamestnanec:');
       // $this->template->mzdovyParAttributes = $this->mzdovyParRepository->getAllMzdovyParAttributesForMzdovyParType($mzdovyParTypeId);
        //$this->zamestnanecId = $zamestnanecId;
        //$this['mzdovyParForm']->setDefaults(['zamestnanecId'=>$zamestnanecId]);

    }


    function renderDetail($mzdovyParId)
    {
        $mzdovyPar = $this->mzdovyParRepository->getMzdovyPar($mzdovyParId);
        if($mzdovyPar){
          $mzdovyParBasicAttributes = $mzdovyPar->toArray();
          $mzdovyParBasicAttributes["zamestnanecName"] = $this->zamestnanecRepository->getName($mzdovyPar->zamestnanec2Id);
          //$mzdovyParBasicAttributes["zamestnanecName"] = $mzdovyPar->zamestnanec->billingFullName;

          $this['mzdovyParForm']->setDefaults($mzdovyParBasicAttributes);
        }else {
          $this->redirect("MzdovyPar:");
        }
    }


    public function handleCreateMzdovyPar()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    protected function createComponentMzdovyParForm()
    {


        $form = $this->mzdovyParFormFactory->create($this->mzdovyParId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Faktura byla aktualizována a změny uloženy.', 'alert-success');
            if($this->zamestnanecId)
              $this->redirect('Zamestnanec:');
            $this->redirect('MzdovyPar:');
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Faktura byla odstraněna.', 'alert-success');
          //  $this->redirect('MzdovyPar:');
          }

        };

        return $form;

    }
    protected function createComponentMzdovyParLogEntryForm()
    {
        $form = $this->mzdovyParLogEntryFormFactory->create($this->mzdovyParId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('MzdovyPar:');
          }
        };
        return $form;

    }




    public function createComponentMzdovyParControl()
    {
        return $this->mzdovyParControlFactory->create();
    }
    public function createComponentRozpadstrediskaControl()
    {
        return $this->rozpadstrediskaControlFactory->create();
    }

    public function actionPrint($mzdovyParId)
    {
      $template = $this->templateFactory->createTemplate();

      $mzdovyPar = $this->mzdovyParRepository->getMzdovyPar($mzdovyParId);
      $template->mzdovyPar = $mzdovyPar;
      $template->zamestnanec = $this->zamestnanecRepository->getZamestnanec($mzdovyPar->zamestnanecId);
      $template->wRs = $this->wRRepository->getAllMzdovyPar($mzdovyParId);

      $formatedId = sprintf("%03d", $mzdovyPar->zamestnanecId);
      $weekCreated = date('W',$mzdovyPar->created->getTimestamp());

      $variabSymbol = "1712".$formatedId.$weekCreated;


      $template->variabSymbol = $variabSymbol;
      $template->dateToPay = date("d. m. Y",$mzdovyPar->created->getTimestamp()+604800+604800);



      $template->setFile(__DIR__ . '/templates/MzdovyPar/pdf.latte');



      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_faktura_".$variabSymbol.".pdf", \Mpdf\Output\Destination::DOWNLOAD);
      //$pdf->Output();

      $this->redirect('MzdovyPar:');
    }

}
