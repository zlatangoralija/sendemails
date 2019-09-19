##SendEmails repository

SendEmail Repository has 3 main functions: 
 1. Send all emails through single SendEmail job, if email fails, save it to database with its content
 2. Get all failed emails from database
 3. Resend single or all failed emails
 
##Package installation
In order to install this package to your project, inside your package.json file, add follwing:

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
 
 Initialize repository where emails are being sent: 
 ```
 $email = new SendEmailRepository;
 ```
 
 Send as much emails as you want! :)
  ```
 $email->sendEmail(collectionOfUsers, EmailClass, Content);
 ```
 
 In order to display routes, create custom vue.js component or blade view.
 
###NOTE 1
Don't forget to publish the package:
 ```
php artisan vendor:publish --provider="Omnitask\SendEmailRepository\Providers\SendEmailServiceProvider"
 ```
 
###NOTE 2
Don't forget to clear application cache before using repository: 
```
composer dump-autoload
php artisan cache:clear
php artisan config:cache
 ```

###NOTE 3
You may also need to manually delete ```bootstrap/cache``` if you get an exception from composer dump autolaod: 
```
Script @php artisan package:discover handling the post-autoload-dump event returned with error code 255
```