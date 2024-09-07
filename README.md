<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Concurrency Handling in Wallet Management System

## Overview
The wallet management system handles concurrent operations through a combination of locking mechanisms and database transactions to ensure data integrity.

## Locking Mechanisms
### Pessimistic Locking
- **Description:** Locks records at the database level to prevent concurrent access.
- **Implementation:** Use `lockForUpdate` method.

### Optimistic Locking
- **Description:** Manages concurrency through versioning.
- **Implementation:** Use a `version` column and check version before updating.

## Database Transactions
- **Description:** Ensures atomicity of deposit and withdrawal operations.
- **Implementation:** Use Laravel's `DB::transaction` method.

## Job Processing and Concurrency
### Job Queuing
- **Description:** Asynchronously processes rebate calculations to avoid blocking.
- **Implementation:** Dispatch jobs using `CalculateRebate::dispatch`.

### Queue Worker
- **Description:** Handles job processing to manage concurrent operations.

## Testing Concurrency
### Unit Tests
- **Description:** Tests verify correct handling of concurrent deposits and withdrawals.
- **Example:** Include scenarios with simultaneous requests to the same wallet.

### Steps
- **Begin a Database Transaction:** Wrap the deposit logic inside a DB::transaction() to ensure that all changes happen atomically. If an error occurs, all changes are rolled back.
- **Lock the Wallet Record:** Use lockForUpdate() to lock the wallet record while performing the balance update.
- **Update the Balance:** Safely increment the wallet’s balance with the deposit amount.
- **Record the Transaction:** Insert a new record into the transactions table.
- **Dispatch Rebate Calculation Job:** After updating the balance, dispatch the CalculateRebate job to process the rebate in the background.

## Test Result
![image](https://github.com/user-attachments/assets/a4a72ca4-4aed-4b45-94e6-bee41500ef02)
