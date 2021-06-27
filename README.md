<!-- PROJECT LOGO -->
<br />
<p align="center">

  <h3 align="center">PHP Wordpress Transaction Modul</h3>

  <p align="center">
    This is a tiny helper class to perform transaction based  queries (ACID) to the wordpress database.  You can use commit and rollback like you are used to do with native database connection.
    <br />
    <a href="https://github.com/Blackstareye/wp_transactionmanager/issues">Report Bug</a>
    ·
    <a href="https://github.com/Blackstareye/wp_transactionmanager/issues">Request Feature</a>
  </p>
</p>

<!-- TABLE OF CONTENTS -->
<details open="open">
  <summary><h2 style="display: inline-block">Table of Contents</h2></summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
    </li>
    <li><a href="#backstory">Backstory</a></li>
    <li><a href="#methods">Methods</a></li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#support">support</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## About The Project

This PHP class will give you the ability to use

* update
* insert
* delete
* vanilla sql
* prepared sql

queries in a transaction based way **in wordpress**. So you are able to manual rollback or commit your queries.

I also implemented a rollback if there were any errors in the execution of the query.
In this case, a rollback will be executed and an error state will be set.
If there is an error state, it is not possible to execute any other queries. This will prevent a inconsistent database.

You have to manually reset it if you want to perform a query in the same task where you encountered the error (not recommended).

### Getting Started

Use the Manager like this:

```php
        //instantiate
        $transaction = new TransactionManager();
        //commit
        $transaction->beginTransaction();
        $transaction->performTransaction_use_query("INSERT INTO $test_table_name (test_data) VALUES (1)");
        $transaction->commit();

        //Rollback
        $transaction->beginTransaction();
        $transaction->performTransaction_use_query("INSERT INTO $test_table_name (test_data) VALUES (0)");
        $transaction->rollback();
```

## Backstory

I realized that Wordpress does not have the ability to perform transaction based data queries.

### Quick The more you know

Still I can't believe that wordpress, a well known blog framework, does not use database transactions.
They just use the autocommit feature of the underlying database.
So if you have 2  update requests on the same data record, you will have a race condition unless wordpress prevents this on the administration page layer.
Also if you have a complex isolated sql query batch to run, you have to delete every affected line if you encountered an error.
**BUT** this is not necessary if you use commit and rollbacks.

### Why do you need that - A little story

So I wanted to implement a Database Migration Tool so  if I have an major update in my plugin which affects the database structure, I can perform a database migration too. But What if it fails or there is a bug? If I would use the vanilla database commands like update_postmeta I  would have to store every affected record and somehow *undo* my actions. This is messy af and also very likely to produce some inconsistency in the database.
So I wanted to have a transaction based behaviour like I am used to in non wordpress applications.

Sidenote:
Yeah my usecase  is an not so common thing todo in wordpress, but I needed it and heres the solution if you also need something like that.

### Methods

#### constructor

If you have a logging handler (like Monolog) you can initialize it here and uncomment the logging lines in the code.

#### resetError

resets the error state.

#### setError

Sets the Manager in error state. Now it is not possible to perform any transaction based queries.

#### error

Increases the error count and (if set) writes an info into the logging file.

#### beginTransaction

This starts the transaction mode. Now every query is not directly commited.

It uses the standard mysql **START TRANSACTION** command.

#### rollback

**Undo's** the queries that were run before between using begin transaction and  rollback.

#### commit

Makes your executed queries persistent.

#### performTransaction_use_prepare

Runs  an [prepared](https://developer.wordpress.org/reference/classes/wpdb/prepare//) SQL query. If it fails it rollbacks. And enables the error state.

#### performTransaction_use_query

Executes the query in transaction mode.
If it fails it rollbacks. And enables the error state.

#### performTransaction_insert

Executes the [wp->insert](https://developer.wordpress.org/reference/classes/wpdb/insert/) in transaction mode.
If it fails it rollbacks. And enables the error state.

#### performTransaction_update

Executes the [wp->update](https://developer.wordpress.org/reference/classes/wpdb/update/) in transaction mode.
If it fails it rollbacks. And enables the error state.

#### performTransaction_delete

Executes the [wp->delete](https://developer.wordpress.org/reference/classes/wpdb/delete/) in transaction mode.
If it fails it rollbacks. And enables the error state.

#### testTransactionModule

This tests if the transaction mode is possible with your underlying db.
You have to use xDebug and your database client so you can monitor if everything runs well.

Only after a commit, the data should be visible.
Neither after a rollback nor before commiting.

## Roadmap

* make the readme prettier
* develop a wordpress plugin with filter-hooks for transaction handling.

See the [open issues](https://github.com/Blackstareye/wp_transactionmanager/issues) for a list of proposed features (and known issues).

<!-- CONTRIBUTING -->
## Contributing

If you have an idea for amazing feature or a nice way to do things more easily in bash you can submit it like the following:

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

Distributed under the GPL version 3. See `LICENSE` for more information.

<!-- Images are licensed under the License [Attribution-NonCommercial-NoDerivatives 4.0 International (CC BY-NC-ND 4.0)](https://creativecommons.org/licenses/by-nc-nd/4.0/) -->

<!-- CONTACT -->
## Contact

Blackeye - [@BlackeyeM](https://twitter.com/BlackeyeM) - private_blackeye+transaction@posteo.de

Project Link: [https://github.com/Blackstareye/wp_transactionmanager](https://github.com/Blackstareye/wp_transactionmanager)

<!-- ACKNOWLEDGEMENTS -->
## Support

If you like what I am doing, you can support me with a little Tip / Donation on those pages:

* [instantgaming](https://www.instant-gaming.com/igr/blackeyes/)  
* [Tipee Donation](https://www.tipeeestream.com/blackeye/donation)
* [BuyMeACoffee](https://www.buymeacoffee.com/Tu6B89Cc3)
* [Patreon](https://www.patreon.com/black_eye_s)

You can also just **share** this repo or any other repo  from me if you like it :) This is also a great kind of support.
