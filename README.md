# MCC-API-Bank
#### _For Laravel 5.1_

#### Installation
1. Install via cli
  1. ```php artisan module:install kylemassacre-mcc/bank```
2. Open up app/Providers/AppServiceProvider.php and add in the register method:
  2.
```php
$this->app->bind([
    ...
    \Modules\Bank\Providers\BankServiceProvider::class
]);
```
3. Run the artisan command:
  3. ```php artisan vendor:publish --provider="Modules\Bank\Providers\BankServiceProvider"```
4. Enjoy!!!
#### API Endpoints:

| Method        | Endpoint                  | Route Name    |
| ------------- |:-------------------------:| -------------:|
|GET/HEAD       |/api/cyberbank/buy         | cyber.buy     |
|POST           |/api/cyberbank/deposit     | cyber.deposit |
|POST           |/api/cyberbank/withdraw    | cyber.withdraw|
|GET/HEAD       |/api/bank/buy              | bank.buy      |
|POST           |/api/bank/deposit          | bank.deposit  |
|POST           | /api/bank/withdraw        | bank.withdraw | 

### Sample Responses
##### Successful Withdraw
```json
{
  "success": {
    "message": "You have withdrew $1 from your account",
    "txn_details": {
      "amount": "$2",
      "past_balance": "$5",
      "new_balance": "$3",
      "fees": "$1"
    }
  }
}
```
##### Successful Deposit
```json
{
  "success": {
    "message": "You have deposited $1 into your account",
    "txn_details": {
      "amount": "$2",
      "past_balance": "$3",
      "new_balance": "$4",
      "fees": "$1"
    }
  }
}
```
##### Invalid Responses
*_Malformed Amount_*
```json
{
  "message": "422 Unprocessable Entity",
  "errors": {
    "amount": [
      "You must provide a valid transaction amount",
      "Your transaction amount must be at least $2"
    ]
  }
  ```
  *_Not enough funds_*
  ```json
  {
    "message": "You don't have enough funds to put that in your bank",
    "status_code": 200,
  }
  ```
  ```json
  {
    "message": "You don't have enough funds in your Cyber Bank",
    "status_code": 200,
  }
  ```
  *_No Bank_*
  ```json
  {
    "message": "You must own a Cyber Bank",
    "status_code": 200,
  }
  ```
  *_Purchase Bank_*
  ```json
{
    "success": "You have purchased a Bank"
}
```
*_Can't afford Bank_*
```json
{
  "message": "You do not have $10,000,000 to open an account",
  "status_code": 200,
}
```
*_Already Own Bank_*
```json
{
  "message": "You already own a Bank",
  "status_code": 200,
}
```