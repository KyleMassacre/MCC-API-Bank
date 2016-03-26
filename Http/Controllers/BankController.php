<?php namespace Modules\Bank\Http\Controllers;

use Api\Controllers\AuthenticatedController;
use Modules\Bank\Http\Requests\BankRequest;

class BankController extends AuthenticatedController {

    /**
     * @var mixed
     */
    private $fee_perc;
    /**
     * @var mixed
     */
    private $max_fee;
    /**
     * @var mixed
     */
    private $cost;


    public function __construct()
    {
        $this->cost = config('bank.bank.bank_cost');
        $this->max_fee = config('bank.bank.bank_maxfee');
        $this->fee_perc = config('bank.bank.bank_feepercent');

        parent::__construct();
    }

    /**
     *
     */
    public function getBuyBank()
    {

        if($this->__hasBank())
        {
            return $this->response->error('You already own a Bank',200);
        }

        elseif($this->user->money < $this->cost)
        {
            return $this->response->error('You do not have '.money_formatter($this->cost).' to open an account',200);
        }

        else
        {
            $this->user->money -= $this->cost;
            $this->user->bankmoney = 0;
            $this->user->save();
            return $this->response->array(['success' => 'You have purchased a Bank']);
        }


    }

    /**
     * @param \Modules\Bank\Http\Requests\BankRequest $request
     */
    public function postDeposit(BankRequest $request)
    {
        $fee = ceil($request->input('amount','0') * $this->fee_perc / 100);
        $maxFee = $this->max_fee;
        $nfee = $fee <= $maxFee ? $fee : $maxFee;

        if(!$this->__hasBank())
        {
            return $this->response->error('You must own a Bank',200);
        }
        elseif($this->user->money < $request->input('amount', '0'))
        {
            return $this->response->error('You don\'t have enough funds to put that in your bank',200);
        }
        else
        {
            $bankBalance = $this->user->bankmoney;
            $gain = $request->input('amount','0') - $nfee;
            $this->user->money -= $request->input('amount','0');
            $this->user->bankmoney += $gain;
            if($this->user->save())
            {
                return $this->response->array([
                    'success' => [
                        'message' => 'You have deposited '.money_formatter($gain).' into your account',
                        'txn_details' => [
                            'amount' => money_formatter($request->input('amount','0')),
                            'past_balance' => money_formatter($bankBalance),
                            'new_balance' => money_formatter($this->user->bankmoney),
                            'fees' => money_formatter($nfee)
                        ]
                    ]
                ]);
            }
            else
            {
                return $this->response->error('An error has occured',200);
            }
        }
    }

    /**
     * @param \Modules\Bank\Http\Requests\BankRequest $request
     */
    public function postWithdraw(BankRequest $request)
    {

        if(!$this->__hasBank())
        {
            return $this->response->error('You must own a Bank',200);
        }
        elseif($this->user->bankmoney < $request->input('amount', '0'))
        {
            return $this->response->error('You don\'t have enough funds in your Bank',200);
        }
        else
        {
            $bankBalance = $this->user->bankmoney;
            $this->user->money += $request->input('amount','0');
            $this->user->bankmoney -= $request->input('amount','0');
            if($this->user->save())
            {
                return $this->response->array([
                    'success' => [
                        'message' => 'You have withdrew '.money_formatter($request->input('amount','0')).' from your account',
                        'txn_details' => [
                            'amount' => money_formatter($request->input('amount','0')),
                            'past_balance' => money_formatter($bankBalance),
                            'new_balance' => money_formatter($this->user->bankmoney),
                        ]
                    ]
                ]);
            }
            else
            {
                return $this->response->error('An error has occured',200);
            }
        }
    }

    /**
     * @return bool
     */
    private function __hasBank()
    {
        return ($this->user->bankmoney >= 0);
    }

	
}