# 3. Задание 02: alpine linux + supervisor (php+nginx) + user + очереди в laravel + мультистейдж сборку

## 3.1. Перевести контейнер с php+nginx на alpine linux: в учебных целях НЕ будем использовать оф образ от php, а используем голый [alpine linux](https://hub.docker.com/_/alpine/tags)
- [x] Сделано

## 3.2. Запускать php и nginx из [supervisor](http://supervisord.org/)
- [x] Сделано

## 3.3. php и nginx должны работать от произвольного пользователя, например, с именем vivek, который не должен обладать привилегиями суперпользователя
- [x] Сделано

## 3.4. Нужно запустить [очереди в laravel](https://laravel.com/docs/11.x/queues) в этом контейнере (что-то выполнять не нужно в очереди, достаточно того чтобы был запущен слушатель)
- [x] Сделано
    * `php artisan make:job ProcessSendingEmail`
    * Открываем созданный файл app/Jobs/ProcessSendingEmail.php и обновляем функцию handle
    * ```    
    public function handle(User $user)
    {
        Log::debug('ProcessSendingEmail:'.date('H:i:s d.m.Y'));                                                       
    }
    ```
    * Отправить новое событие в очередь, `routes/web.php`:
    * ```
    use App\Jobs\ProcessSendingEmail;
    use App\Jobs\ProcessPodcast;
    use Illuminate\Support\Facades\Route;
    
    Route::get('/', function () {
       ProcessSendingEmail::dispatch();
       ProcessPodcast::dispatch();
       return view('welcome');
    });
    ```
    * После вызова функции dispatch новое событие тут же улетит в нашу очередь, а нам лишь остаётся запустить обработчик нашей очереди и ждать, когда письмо отправится пользователю.
    * Запускаем обработчик всех событий в консоли командой:
    * `php artisan queue:work`
    * В данном случаем обработчик запущен в `deploy/supervisord.ini`
    * ```
    [program:laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/html/artisan queue:work
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/var/www/html/storage/logs/worker.log
    stopwaitsecs=3600
    stdout_logfile_maxbytes=5MB
    ```
    * После запуска обработчика все наши события будут поочередно обрабатываться.

## 3.5. Добавить мультистейдж сборку приложения
- [x] В работе

* Изучение:
    * https://selectel.ru/blog/docker-security-2/
    * Механизм мультистейдж (multi-stage) позволяет избавиться от всего лишнего и собрать итоговый образ, исключающий build-зависимости и выборочно копирующий только необходимые артефакты. В итоге мы получаем временные образы и финальный, а поверхность атаки значительно уменьшается. Также сам образ будет меньше — значит, сокращается время, которое нужно на его загрузку.

* Практически каждая инструкция в Dockerfile добавляет отдельный слой и вам необходимо очистить этот слой от всех лишних артефактов, перед тем как добавить новый слой. Поэтому чтобы создать действительно эффективный Dockerfile раньше вам традиционно предлагали использовать скрипты и другую логику, чтобы поддерживать минимально возможный размер слоя. Обычной практикой было использовалось несколько Dockerfile в зависимости от целей сборки образа — один файл для DEVELOPMENT с определенным набором средства для отладки, профайлинга и всего остального и другой образ, гораздо меньшего размера для развертывания приложения на STAGING или PRODUCTION, с набором компонентов, необходимых для работы приложения.
0. https://www.itsumma.ru/blog/docker_image, https://dev.to/titasgailius/multi-stage-docker-builds-for-laravel-c86
1. указать исключения: что не надо тащить в контекст. Для этого положим в проект файл .dockerignore и укажем, что не нужно для сборки


# Работа с Laravel

composer create-project --prefer-dist laravel/laravel=^11.0 ./larr11

docker exec -t laravelapp php artisan key:generate

docker exec -t laravelapp php artisan optimize

docker exec -t laravelapp php artisan make:queue-table

docker exec -t laravelapp php artisan migrate

docker exec -t laravelapp php artisan config:cache

php artisan make:job ProcessSendingEmail
