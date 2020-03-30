<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\OrderRepository;
use App\Model\Repository\CustomerRepository;
use Nette\Security\User;

use Tracy\Debugger;

Debugger::enable();



class OrderControl extends Control
{

    private $orderRepository;
    private $customerRepository;

    /** @var User */
    private $user;

    /** @persistent */
    public $customerId;

    public function __construct(OrderRepository $orderRepository, CustomerRepository $customerRepository, Nette\Security\User $user)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->user = $user;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->orderTypeFilterId;
        //$this->template->orderTypeFilterId = $this->type;
        //$this->template->orderTypes = $this->orderRepository->getAllOrderTypes();
        $this->template->render(__DIR__ . '/order_control.latte');

    }


    public function createComponentOrderGrid($name)
    {
        $this->customerId =$this->getPresenter()->customerId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('orderId');


        if(isset($this->getPresenter()->invoiceId))
          $grid->setDataSource($this->orderRepository->getAllInvoice($this->getPresenter()->invoiceId));
        else {
          $grid->setDataSource($this->orderRepository->getAllCustomer($this->customerId));
        }

        $grid->addColumnLink('orderId', 'číslo objednávky',  'Order:detail','orderId',['orderId'=>'orderId'])->setSortable();
        $grid->addColumnDateTime('created', 'vytvořeno')->setFormat('d.m.Y')->setSortable();
        $grid->addColumnLink('fk_customer', 'zákazník','Customer:detail','fk_customer',['customerId'=>'fk_customer'])->setReplacement($this->customerRepository->getCustomerNames());
        $grid->addColumnLink('list', 'nákupní seznam', 'Order:detail','list',['orderId'=>'orderId'])->setSortable();
        $grid->addColumnText('price', 'cena')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnLink('fk_invoice', 'vyfakturováno', 'Invoice:detail','fk_invoice',['invoiceId'=>'fk_invoice'])->addAttributes(["width" => "15%"])->setReplacement(['0'=>'ne'])->setSortable();
        // $grid->addColumnText('billingZip', 'psč')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"]);
        $grid->addAction('link2', 'Uložit pdf', 'Order:print');
        $grid->addAction('link', 'Detail', 'Order:detail');


        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['orderId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }
    public function priceEdited($id,$value)
    {
      return $this->orderRepository->updatePrice($id,$value, $this->user->getId());
    }


}
