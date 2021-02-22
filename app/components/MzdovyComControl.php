<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\MzdovyComRepository;
use App\Model\Repository\zamestnanecRepository;

use Tracy\Debugger;

Debugger::enable();



class MzdovyComControl extends Control
{

    private $mzdovyComRepository;
    private $zamestnanecRepository;

    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    public function __construct(MzdovyComRepository $mzdovyComRepository, ZamestnanecRepository $zamestnanecRepository)
    {
        parent::__construct();
        $this->mzdovyComRepository = $mzdovyComRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->mzdovyComTypeFilterId;
        //$this->template->mzdovyComTypeFilterId = $this->type;
        //$this->template->mzdovyComTypes = $this->mzdovyComRepository->getAllMzdovyComTypes();
        $this->template->render(__DIR__ . '/mzdovyCom_control.latte');

    }


    public function createComponentMzdovyComGrid($name)
    {
        $this->zamestnanecId =$this->getPresenter()->zamestnanecId;
        $this->pomerId = $this->getPresenter()->pomerId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('mzdovyComId');

        if($this->pomerId)
          $grid->setDataSource($this->mzdovyComRepository->getAllPomer($this->pomerId));
        else
          $grid->setDataSource($this->mzdovyComRepository->getAllZamestnanec($this->zamestnanecId));

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
         $grid->addColumnLink('mzdovyComId', 'číslo mzdoveho listu', 'MzdovyCom:detail','mzdovyComId',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "10%"]);
        // $grid->addColumnLink('created', 'vytvořeno', 'MzdovyCom:detail','created',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "25%"]);
        $grid->addColumnDateTime('periodId', 'měsíc')->setSortable();

        //$grid->addColumnText('created', 'vytvořeno')->addAttributes(["width" => "25%"])->setFilterText();
        $grid->addColumnLink('zamestnanecId', 'Zaměstnanec','Zamestnanec:detail','zamestnanecId',['pomerId'=>'zamestnanecId'])->addAttributes(["width" => "25%"]);
        $grid->addColumnLink('hrubaMzda', 'hrubá mzda','MzdovyCom:detail','hrubaMzda',['mzdovyComId'=>'mzdovyComId'])->setSortable();

        //$grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"])->setFilterText();
        //  $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"])->setFilterText();
      //  $grid->addAction('link', 'Přidat nákup', 'MzdovyCom:create')->addAttributes(["width" => "25%"]);
        // $grid->addAction('link2', 'Uložit pdf', 'MzdovyCom:print')->addAttributes(["width" => "25%"]);
        // $grid->addAction('link', 'Detail', 'MzdovyCom:detail');

        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['mzdovyComId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
