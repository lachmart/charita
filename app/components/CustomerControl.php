<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\CustomerRepository;
use App\Model\Repository\OrderRepository;

use Tracy\Debugger;

Debugger::enable();



class CustomerControl extends Control
{

    private $customerRepository;
    private $orderRepository;

    public function __construct(CustomerRepository $customerRepository, OrderRepository $orderRepository)
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->customerTypeFilterId;
        //$this->template->customerTypeFilterId = $this->type;
        //$this->template->customerTypes = $this->customerRepository->getAllCustomerTypes();
        $this->template->render(__DIR__ . '/customer_control.latte');

    }


    public function createComponentCustomerGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('customerId');

        $grid->setDataSource($this->customerRepository->getAll());

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnLink('customerId', 'číslo zákazníka', 'detail','customerId',['customerId'=>'customerId'])->setSortable()->addAttributes(["width" => "10%"])->setFilterText();
        $grid->addColumnLink('billingFullName', 'Zákazník', 'Customer:detail','billingFullName',['customerId'=>'customerId'])->setSortable()->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnLink('billingStreet', 'ulice , číslo','Customer:detail','billingStreet',['customerId'=>'customerId'])->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnLink('billingCity', 'město', 'Customer:detail','billingCity',['customerId'=>'customerId'])->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnLink('billingZip', 'psč', 'Customer:detail','billingZip',['customerId'=>'customerId'])->addAttributes(["width" => "7%"])->setFilterText();
        $grid->addColumnLink('email', 'email', 'Customer:detail','email',['customerId'])->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnLink('phone', 'telefon','Customer:detail','phone',['customerId'=>'customerId'])->addAttributes(["width" => "10%"])->setFilterText();
        $grid->addAction('send', 'Vystavit fakturu', 'Invoice:create')
        ->setRenderer(
          function($item) {return '';},
          function($item) {return count($this->orderRepository->getOrdersWithoutInvoice($item->customerId))?false:true;}
          )
         ->setConfirm(
		           'Opravdu vystavit fakturu? Tuto akci nelze vrátit.'// Second parameter is optional
	            );
        $grid->addAction('link2', 'Přidat objednávku', 'Order:create');
        $grid->addAction('link', 'Detail', 'Customer:detail');


        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setDefaultSort(['customerId' => 'DESC']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
