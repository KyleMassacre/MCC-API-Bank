<?php namespace Modules\Bank\Http\Controllers;

use App;
use Modules\Bank\Http\Requests\BankRequest;
use Api\Controllers\AuthenticatedController;


class CyberBankController extends AuthenticatedController {

    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private $city;

    /**
     * @var mixed
     */
    private $cost;

    /**
     * @var mixed
     */
    private $max_dp_fee;

    /**
     * @var mixed
     */
    private $fee_pc_dp;

    /**
     * @var mixed
     */
    private $max_wd_fee;

    /**
     * @var mixed
     */
    private $fee_pc_wd;


    public function __construct()
    {
        $this->cost = config('bank.cyber_bank.cost');

        $this->max_dp_fee = config('bank.cyber_bank.max_fee_dp');

        $this->fee_pc_dp = config('bank.cyber_bank.feepercent_dp');

        $this->max_wd_fee = config('bank.cyber_bank.maxfee_wd');

        $this->fee_pc_wd = config('bank.cyber_bank.feepercent_wd');

        $this->city = App\City::find(config('bank.cyber_bank.location'));

        parent::__construct();

    }

    /**
     * @return mixed
     */
    public function getBuyCyberBank()
    {
        if(!$this->__canAccess())
        {
            return $this->response->error('You must be in '.$this->city->cityname,200);
        }

        if($this->__hasCyberBank())
        {
            return $this->response->error('You already own a Cyber Bank',200);
        }

        elseif($this->user->money < $this->cost)
        {
            return $this->response->error('You do not have '.money_formatter($this->cost).' to open an account',200);
        }

        else
        {
            $this->user->money -= $this->cost;
            $this->user->cybermoney = 0;
            $this->user->save();
            return $this->response->array(['success' => 'You have purchased a Cyber Bank']);
        }


	}

    /**
     * @param \Modules\Bank\Http\Requests\BankRequest $request
     */
    public function postDeposit(BankRequest $request)
    {
        if(!$this->__canAccess())
        {
            return $this->response->error('You must be in '.$this->city->cityname,200);
        }

        $fee = ceil($request->input('amount','0') * $this->fee_pc_dp / 100);
        $maxFee = $this->max_dp_fee;
        $nfee = $fee <= $maxFee ? $fee : $maxFee;

        if(!$this->__hasCyberBank())
        {
            return $this->response->error('You must own a Cyber Bank',200);
        }
        elseif($this->user->money < $request->input('amount', '0'))
        {
            return $this->response->error('You don\'t have enough funds to put that in your bank',200);
        }
        else
        {
            $cyberBalance = $this->user->cybermoney;
            $gain = $request->input('amount','0') - $nfee;
            $this->user->money -= $request->input('amount','0');

            $this->user->cybermoney += $gain;
            if($this->user->save())
            {
                return $this->response->array([
                    'success' => [
                        'message' => 'You have deposited '.money_formatter($gain).' into your account',
                        'txn_details' => [
                            'amount' => money_formatter($request->input('amount','0')),
                            'past_balance' => money_formatter($cyberBalance),
                            'new_balance' => money_formatter($this->user->cybermoney),
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
        if(!$this->__canAccess())
        {
            return $this->response->error('You must be in '.$this->city->cityname,200);
        }

        $fee = ceil($request->input('amount','0') * $this->fee_pc_wd / 100);
        $maxFee = $this->max_wd_fee;
        $nfee = $fee <= $maxFee ? $fee : $maxFee;

        if(!$this->__hasCyberBank())
        {
            return $this->response->error('You must own a Cyber Bank',200);
        }
        elseif($this->user->cybermoney < $request->input('amount', '0'))
        {
            return $this->response->error('You don\'t have enough funds in your Cyber Bank',200);
        }
        else
        {
            $gain = $request->input('amount','0') - $nfee;
            $cyberBalance = $this->user->cybermoney;
            $this->user->money += $request->input('amount','0') - $nfee;
            $this->user->cybermoney -= $request->input('amount','0');
            if($this->user->save())
            {
                return $this->response->array([
                    'success' => [
                        'message' => 'You have withdrew '.money_formatter($gain).' from your account',
                        'txn_details' => [
                            'amount' => money_formatter($request->input('amount','0')),
                            'past_balance' => money_formatter($cyberBalance),
                            'new_balance' => money_formatter($this->user->cybermoney),
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
     * @return bool
     */
    private function __hasCyberBank()
    {
        return ($this->user->cybermoney >= 0);
    }

    /**
     * @return bool
     */
    private function __canAccess()
    {
        if($this->user->location != $this->city->cityid)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
	
}