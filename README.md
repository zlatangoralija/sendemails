##Omnitask\SendEmails repository

SendEmail Repository has 4 main functions: 
 1. Send all emails through single SendEmail job
 2. Email throttling, where you can specify how many emails you want to send in *n* seconds
 3. Catch all failed emails and store them in database with additional info
 4. Resend single or all failed emails  
 
## Package installation
In order to install this package to your project, inside your `composer.json` file, add following:

```
  "require": {
    "omnitask/send-emails": "dev-master"
  },
```
```
  "repositories":[
    {
      "type": "vcs",
      "url": "git@git.omnitask.ba:zlatan/sendemails.git",
      "options": {
        "ssh2": {
          "username": "composer",
          "pubkey_file": "/home/composer/.ssh/id_rsa.pub",
          "privkey_file": "/home/composer/.ssh/id_rsa"
        }
      }
    }
  ]
 ```

Note: If you're already using `Omnitask\SendEmails` and you want to upgrade package version, you need to change package version in your composer.json file:
`"omnitask/send-emails": "1.2.1"`

 After installation, necessary steps to use this repository are following: <br>
 
 Run migrations:
 ```
 php artisan migrate
 ```
 
 Add relation inside User model:
  ```
 public function failed_emails(){
    return $this->hasMany(ResendEmail::class,'user_id');
 }
  ```
 
 Initialize the repository where emails are being sent: 
 ```
 $email = new SendEmailRepository;
 ```
 
 Send as many emails as you want! :)
  ```
 $email->sendEmail(collectionOfUsers, EmailClass, Content);
 ```
 
 In order to display routes, create custom vue.js component or blade view.
 
### Configuration
There are many options for Redis throttling configuration. To apply these to your queues, add following keys to your `.env` file: 
- `REDIS_SEND_MAX_EMAILS` - Max. amount of emails that are being sent 
- `REDIS_SEND_EMAILS_EVERY_N_SECONDS` - Send above configured max. emails every *n* seconds
- `REDIS_RELEASE_FAILED_EMAILS_BACK_TO_QUEUE_DELAY` - Release failed jobs back to queue after after specified time in seconds
- `SEND_EMAILS_QUEUE` - Custom Redis queue name, if you're not using the default one.

If you don't provide configuration options above, the default values are:
- `REDIS_SEND_MAX_EMAILS = 1` 
- `REDIS_SEND_EMAILS_EVERY_N_SECONDS = 11`
- `REDIS_RELEASE_FAILED_EMAILS_BACK_TO_QUEUE_DELAY = 10`
- `SEND_EMAILS_QUEUE = 'default'`

 
### NOTE 1
Don't forget to publish the package configration:
 ```
php artisan vendor:publish --provider="Omnitask\SendEmailRepository\Providers\SendEmailServiceProvider"
 ```
 
### NOTE 2
Don't forget to clear application cache before using repository: 
```
composer dump-autoload
php artisan cache:clear
php artisan config:cache
 ```

### NOTE 3
You may also need to manually delete ```bootstrap/cache``` if you get an exception from composer dump-autolaod: 
```
Script @php artisan package:discover handling the post-autoload-dump event returned with error code 255
```