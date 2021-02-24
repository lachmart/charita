<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\MzdovyParRepository;
use App\Model\Repository\zamestnanecRepository;

use Tracy\Debugger;

Debugger::enable();



class MzdovyParControl extends Control
{

    private $mzdovyParRepository;
    private $zamestnanecRepository;

    /** @persistent */
    public $mzdovyComId;
    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    public function __construct(MzdovyParRepository $mzdovyParRepository, ZamestnanecRepository $zamestnanecRepository)
    {
        parent::__construct();
        $this->mzdovyParRepository = $mzdovyParRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->mzdovyParTypeFilterId;
        //$this->template->mzdovyParTypeFilterId = $this->type;
        //$this->template->mzdovyParTypes = $this->mzdovyParRepository->getAllMzdovyParTypes();
        $this->template->render(__DIR__ . '/mzdovyPar_control.latte');

    }


    public function createComponentMzdovyParGrid($name)
    {
        $this->zamestnanecId =$this->getPresenter()->zamestnanecId;
        $this->pomerId = $this->getPresenter()->pomerId;
        $this->mzdovyComId = $this->getPresenter()->mzdovyComId??null;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('mzdovyParId');

        if($this->mzdovyComId)
          $grid->setDataSource($this->mzdovyParRepository->getAllMzdovy($this->mzdovyComId));
        elseif($this->pomerId)
          $grid->setDataSource($this->mzdovyParRepository->getAllPomer($this->pomerId));
        else
          $grid->setDataSource($this->mzdovyParRepository->getAllZamestnanec($this->zamestnanecId));

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
         $grid->addColumnLink('mzdovyParId', 'číslo mzdoveho listu', 'MzdovyPar:detail','mzdovyParId',['mzdovyParId'=>'mzdovyParId'])->setSortable()->addAttributes(["width" => "10%"]);
        // $grid->addColumnLink('created', 'vytvořeno', 'MzdovyPar:detail','created',['mzdovyParId'=>'mzdovyParId'])->setSortable()->addAttributes(["width" => "25%"]);
        $grid->addColumnDateTime('periodId', 'měsíc')->setSortable();

        //$grid->addColumnText('created', 'vytvořeno')->addAttributes(["width" => "25%"])->setFilterText();
        $grid->addColumnLink('zamestnanec2Id', 'Zaměstnanec','Zamestnanec:detail','zamestnanec2Id',['pomerId'=>'zamestnanec2Id'])->addAttributes(["width" => "25%"]);
        $grid->addColumnLink('hrubaMzda', 'hrubá mzda','MzdovyPar:detail','hrubaMzda',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('mesicniOdmeny', 'odměny','MzdovyPar:detail','mesicniOdmeny',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('premie', 'premie','MzdovyPar:detail','premie',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('trinactyPlat', 'trinactyPlat','MzdovyPar:detail','trinactyPlat',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('odmenaZaDelsiOdbobi', 'odmenaZaDelsiOdbobi','MzdovyPar:detail','odmenaZaDelsiOdbobi',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('odpracovaneHodinyVcetnePrescasu', 'odpracovaneHodinyVcetnePrescasu','MzdovyPar:detail','odpracovaneHodinyVcetnePrescasu',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('zTohoPrescas', 'zTohoPrescas','MzdovyPar:detail','zTohoPrescas',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('placeneVolno', 'placeneVolno','MzdovyPar:detail','placeneVolno',['mzdovyParId'=>'mzdovyParId'])->setSortable();
        $grid->addColumnLink('nemoc', 'nemoc','MzdovyPar:detail','nemoc',['mzdovyParId'=>'mzdovyParId'])->setSortable();

        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['mzdovyParId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
