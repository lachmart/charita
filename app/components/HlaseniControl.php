<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\HlaseniRepository;
use App\Model\Repository\WRRepository;

use Tracy\Debugger;

Debugger::enable();



class HlaseniControl extends Control
{

    private $hlaseniRepository;
    private $wRRepository;

    public function __construct(HlaseniRepository $hlaseniRepository, WRRepository $wRRepository)
    {
        parent::__construct();
        $this->hlaseniRepository = $hlaseniRepository;
        $this->wRRepository = $wRRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->hlaseniTypeFilterId;
        //$this->template->hlaseniTypeFilterId = $this->type;
        //$this->template->hlaseniTypes = $this->hlaseniRepository->getAllHlaseniTypes();
        $this->template->render(__DIR__ . '/hlaseni_control.latte');

    }


    public function createComponentHlaseniGrid($name)
    {

        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('hlaseniId');

        if(isset($this->getPresenter()->zamestnanecId) && $this->getPresenter()->zamestnanecId) {
            $zamestnanecId = $this->getPresenter()->zamestnanecId;
            $grid->setDataSource($this->hlaseniRepository->getAllZamestnanec($zamestnanecId));
        }else
            $grid->setDataSource($this->hlaseniRepository->getAll());

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnLink('hlaseniId', 'číslo zaměstnaneca', 'detail','hlaseniId',['hlaseniId'=>'hlaseniId'])->setSortable()->addAttributes(["width" => "10%"])->setFilterText();
        $grid->addColumnLink('zamestnanecId', 'Osoba', 'Hlaseni:detail','zamestnanecId',['hlaseniId'=>'hlaseniId'])->setSortable()->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnText('typPomeru', 'typPomeru')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('velikostPomeru', 'velikostPomeru')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('typPozice', 'typPozice')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('typUdalosti', 'typUdalosti')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('it', 'it')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('vizitky', 'vizitky')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('BOZP', 'BOZP')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('PO', 'PO')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('ridici', 'ridici')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('univerzalniKlic', 'univerzalniKlic')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('poznamka', 'poznamka')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnText('datum', 'datum')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnStatus('status', 'status')
            ->addOption(1, 'Přijato')
            ->endOption()
            ->addOption(0, 'Vytvořeno')
            ->endOption();

        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setDefaultSort(['hlaseniId' => 'DESC']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
