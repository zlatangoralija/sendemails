##Omnitask\SendEmails repository

SendEmail Repository has 4 main functions: 
 1. Send emails through SendEmail job
 2. Catch all failed emails and store them in database with additional info
 3. Get all failed emails from database
 4. Resend failed emails
 
##Package installation
In order to install this package to your project, inside your package.json file, add following:

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

Note: If you're already using Omnitask\SendEmails and you want to upgrade package version, 
you need to change package version in your composer.json file:
 ```
 "omnitask/send-emails": "1.1.0-beta",
 ```

After installation, necessary steps to use this repository are following: <br>
 
 Run migrations:
 ```
 php artisan migrate
 ```

If you're using older Laravel versions, don't forget to publish the package:
 ```
php artisan vendor:publish --provider="Omnitask\SendEmailRepository\Providers\SendEmailServiceProvider"
 ```

Optional:  Add relation inside User model:
```
public function failed_emails(){
  return $this->hasMany(ResendEmail::class,'user_id');
}
```
 
###Usage

####Sending emails
In order to send emails, first initialize the repository: 
 ```
 $email = new SendEmailRepository();
 ```

After that, use ```sendEmailByMailable``` function to send emails:

  ```
 $email->sendEmailByMailable($user, EmailClass, Model);
 ``` 

Function accepts 3 parameters: 
1. User - instance of ```User``` model
2. EmailClass - pre-built mailable class (```App/Mail```)
3. Model - Instance of ```App/Model``` that contains additional data that is sent through mailable class

Third parameter is not required, and you can send email with just ```EmailClass```:

```
$email->sendEmailByMailable($user, new EmailClass());
```

####Getting failed emails
In order to fetch failed emails, there are some helper static functions:
```
SendEmailRepository::getAllFailedEmails() //Get all failed emails
SendEmailRepository::getFailedEmailsByModelName($modelName) //Get all failed emails with matching model_class name 
SendEmailRepository::getFailedEmailByModelId($modelName, $modelId) //Get single failed email with matching model_class and model_id 
SendEmailRepository::getMailableClassName($mailable) //Get Mailable class name as a string
```
 
####Resend failed emails
In order to resend previously failed emails, you may use the ```resendEmailByMailable()``` function:
  ```
 $email->resendEmailByMailable($failedEmail);
 ``` 

Function accepts one parameter, that is an instance of ResendEmail class, which you can get through previously mentioned helpers.


###Additional notes
Don't forget to clear application cache before using repository: 
```
composer dump-autoload
php artisan cache:clear
php artisan config:cache
 ```

You may also need to manually delete ```bootstrap/cache``` if you get an exception from ```composer dump-autolaod``` command: 
```
Script @php artisan package:discover handling the post-autoload-dump event returned with error code 255
```