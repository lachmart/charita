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
use App\Model\Repository\HlaseniRepository;
use App\Model\Repository\WRRepository;
use App\Forms\IHlaseniFormFactory;
use App\Forms\HlaseniForm;
use App\Components\IHlaseniControlFactory;
use App\Components\IWRControlFactory;
use App\Components\IMzdovyComControlFactory;

//define("ALL", -1);

class HlaseniPresenter extends BasePresenter
{
    /** @var HlaseniRepository */
    private $hlaseniRepository;

    /** @var WRRepository */
    private $wRRepository;

    /**
     * @var IHlaseniControlFactory
     * @inject
     */
    public $hlaseniControlFactory;

    /**
     * @var IMzdovyComControlFactory
     * @inject
     */
    public $mzdovyComControlFactory;
    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;

    /** @var \IHlaseniFormFactory */
    private $hlaseniFormFactory;

    /** @persistent */
    public $pomerId;

    /** @persistent */
    public $hlaseniId;
    /** @persistent */
    public $zamestnanecId;

    public function __construct(HlaseniRepository $hlaseniRepository,WRRepository $wRRepository,
                                HlaseniForm $hlaseniFormFactory)
    {
        parent::__construct();
        $this->hlaseniRepository = $hlaseniRepository;
        $this->wRRepository = $wRRepository;
        $this->hlaseniFormFactory =  $hlaseniFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->hlaseniTypeFilterId = ALL;

    }


    function renderDefault()
    {
        //$this->template->hlaseniTypes = $this->hlaseniRepository->getAllHlaseniTypes();
        //\var_dump($this->user->getIdentity());
    }


    function renderCreate($zamestnanecId)
    {
      $this->template->showMzdovyComCreate = 0;
      $this->zamestnanecId = $zamestnanecId;
       // $this->template->hlaseniAttributes = $this->hlaseniRepository->getAllHlaseniAttributesForHlaseniType($hlaseniTypeId);
        //$this->hlaseniTypeId = $hlaseniTypeId;
    }


    function renderDetail($hlaseniId)
    {
        $this->template->showMzdovyComCreate = 0;
        $hlaseni = $this->hlaseniRepository->get($hlaseniId);
        if($hlaseni){
          $this->hlaseniId = $hlaseniId;
          $this->template->hlaseni = $hlaseni;
          //$this->template->showMzdovyComCreate = count($this->wRRepository->getWRsWithoutMzdovyCom($hlaseniId));
          $hlaseniBasicAttributes = $hlaseni->toArray();
          $this['hlaseniForm']->setDefaults($hlaseniBasicAttributes);

        }else {
          $this->redirect("Hlaseni:");
        }
    }


    public function handleCreateHlaseni()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    public function handleCreateLogEntry()
    {
      //  if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
      //  }
    }

    protected function createComponentHlaseniForm()
    {

        //TODO hlaseni
        //$hlaseniTypeId = $this->hlaseniTypeId;


       // if(!$hlaseniTypeId) $hlaseniTypeId = 0;

        $form = $this->hlaseniFormFactory->create($this->zamestnanecId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            if($this->hlaseniId){
              $this->flashMessage('Zaměstnanec byl aktualizován a změny uloženy.', 'alert-success');
              $this->redirect('Hlaseni:detail');
            }
            else{
              $this->flashMessage('Hlášení bylo uloženo.', 'alert-success');
              $this->redirect('Zamestnanec:detail', $this->zamestnanecId);
            }
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Zaměstnanec byl odstraněn.', 'alert-success');
            $this->redirect('Hlaseni:');
          }

        };



        return $form;

    }
    protected function createComponentHlaseniLogEntryForm()
    {
        $form = $this->hlaseniLogEntryFormFactory->create($this->hlaseniId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('Hlaseni:');
          }
        };
        return $form;

    }


    public function createComponentHlaseniControl()
    {
        return $this->hlaseniControlFactory->create();
    }


}
