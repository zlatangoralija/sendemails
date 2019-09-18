##SendEmails repository

SendEmail Repository has 3 main functions: 
 1. Send all emails through single SendEmail job, if email fails, save it to database
 2. Get all failed emails from database
 3. Resend single or all failed emails
 
 Necessary steps to use this repository: <br>
 
 Run migrations:
 ```
 php artisan migrate
 ```
 Register SendEmailRepository service provider inside config/app: <br>
 ```
 'providers' => [
    App\Providers\SendEmailServiceProvider::class,
  ];
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
###NOTE
Don't forget to clear application cache before using repository: 
```
composer dump-autoload
php artisan cache:clear
php artisan config:cache
 ```