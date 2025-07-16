<?php

class Pages extends Controller
{

    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }

    public function index()
    {
        $this->view('pages/login');
    }

    public function login()
    {
        $this->view('pages/login');
    }

    public function register()
    {
        $this->view('pages/register');
    }

    public function about()
    {
        $this->view('pages/about');
    }

    public function dashboard()
    {
        $income = $this->db->incomeTransition();
        $expense = $this->db->expenseTransition();

        $data = [
            'income' => isset($income['amount']) ? $income : ['amount' => 0],
            'expense' => isset($expense['amount']) ? $expense : ['amount' => 0]
        ];

        $this->view('pages/dashboard', $data);
    }

}
