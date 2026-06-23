# Laravel Best Practices

> Source: [alexeymezenin/laravel-best-practices](https://github.com/alexeymezenin/laravel-best-practices)
> Project-local reference for this project.

You might also want to check out the [real-world Laravel example application](https://github.com/alexeymezenin/laravel-realworld-example-app) and [Eloquent SQL reference](https://github.com/alexeymezenin/eloquent-sql-reference)

## Contents

- [Single responsibility principle](#single-responsibility-principle)
- [Methods should do just one thing](#methods-should-do-just-one-thing)
- [Fat models, skinny controllers](#fat-models-skinny-controllers)
- [Validation](#validation)
- [Business logic should be in service class](#business-logic-should-be-in-service-class)
- [Don't repeat yourself (DRY)](#dont-repeat-yourself-dry)
- [Prefer to use Eloquent over using Query Builder and raw SQL queries. Prefer collections over arrays](#prefer-to-use-eloquent-over-using-query-builder-and-raw-sql-queries-prefer-collections-over-arrays)
- [Mass assignment](#mass-assignment)
- [Do not execute queries in Blade templates and use eager loading (N + 1 problem)](#do-not-execute-queries-in-blade-templates-and-use-eager-loading-n--1-problem)
- [Chunk data for data-heavy tasks](#chunk-data-for-data-heavy-tasks)
- [Prefer descriptive method and variable names over comments](#prefer-descriptive-method-and-variable-names-over-comments)
- [Do not put JS and CSS in Blade templates and do not put any HTML in PHP classes](#do-not-put-js-and-css-in-blade-templates-and-do-not-put-any-html-in-php-classes)
- [Use config and language files, constants instead of text in the code](#use-config-and-language-files-constants-instead-of-text-in-the-code)
- [Use standard Laravel tools accepted by community](#use-standard-laravel-tools-accepted-by-community)
- [Follow Laravel naming conventions](#follow-laravel-naming-conventions)
- [Convention over configuration](#convention-over-configuration)
- [Use shorter and more readable syntax where possible](#use-shorter-and-more-readable-syntax-where-possible)
- [Use IoC / Service container instead of new Class](#use-ioc--service-container-instead-of-new-class)
- [Do not get data from the `.env` file directly](#do-not-get-data-from-the-env-file-directly)
- [Store dates in the standard format. Use accessors and mutators to modify date format](#store-dates-in-the-standard-format-use-accessors-and-mutators-to-modify-date-format)
- [Do not use DocBlocks](#do-not-use-docblocks)
- [Other good practices](#other-good-practices)

---

### **Single responsibility principle**

A class should have only one responsibility.

Bad:

```php
public function update(Request $request): string
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'events' => 'required|array:date,type'
    ]);

    foreach ($request->events as $event) {
        $date = $this->carbon->parse($event['date'])->toString();
        $this->logger->log('Update event ' . $date . ' :: ' . $);
    }

    $this->event->updateGeneralEvent($request->validated());
    return back();
}
```

Good:

```php
public function update(UpdateRequest $request): string
{
    $this->logService->logEvents($request->events);
    $this->event->updateGeneralEvent($request->validated());
    return back();
}
```

[🔝 Back to contents](#contents)

### **Methods should do just one thing**

A function should do just one thing and do it well.

Bad:

```php
public function getFullNameAttribute(): string
{
    if (auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified()) {
        return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
    } else {
        return $this->first_name[0] . '. ' . $this->last_name;
    }
}
```

Good:

```php
public function getFullNameAttribute(): string
{
    return $this->isVerifiedClient() ? $this->getFullNameLong() : $this->getFullNameShort();
}

public function isVerifiedClient(): bool
{
    return auth()->user() && auth()->user()->hasRole('client') && auth()->user()->isVerified();
}

public function getFullNameLong(): string
{
    return 'Mr. ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
}

public function getFullNameShort(): string
{
    return $this->first_name[0] . '. ' . $this->last_name;
}
```

[🔝 Back to contents](#contents)

### **Fat models, skinny controllers**

Put all DB related logic into Eloquent models.

Bad:

```php
public function index()
{
    $clients = Client::verified()
        ->with(['orders' => function ($q) {
            $q->where('created_at', '>', Carbon::today()->subWeek());
        }])
        ->get();
    return view('index', ['clients' => $clients]);
}
```

Good:

```php
public function index()
{
    return view('index', ['clients' => $this->client->getWithNewOrders()]);
}

class Client extends Model
{
    public function getWithNewOrders(): Collection
    {
        return $this->verified()
            ->with(['orders' => function ($q) {
                $q->where('created_at', '>', Carbon::today()->subWeek());
            }])
            ->get();
    }
}
```

[🔝 Back to contents](#contents)

### **Validation**

Move validation from controllers to Request classes.

Bad:

```php
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
        'publish_at' => 'nullable|date',
    ]);
    // ...
}
```

Good:

```php
public function store(PostRequest $request)
{
    // ...
}

class PostRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
            'publish_at' => 'nullable|date',
        ];
    }
}
```

[🔝 Back to contents](#contents)

### **Business logic should be in service class**

Move business logic from controllers to service classes.

Bad:

```php
public function store(Request $request)
{
    if ($request->hasFile('image')) {
        $request->file('image')->move(public_path('images') . 'temp');
    }
    // ...
}
```

Good:

```php
public function store(Request $request)
{
    $this->articleService->handleUploadedImage($request->file('image'));
    // ...
}

class ArticleService
{
    public function handleUploadedImage($image): void
    {
        if (!is_null($image)) {
            $image->move(public_path('images') . 'temp');
        }
    }
}
```

[🔝 Back to contents](#contents)

### **Don't repeat yourself (DRY)**

Reuse code when you can. Use Blade templates, Eloquent scopes, etc.

Bad:

```php
public function getActive()
{
    return $this->where('verified', 1)->whereNotNull('deleted_at')->get();
}

public function getArticles()
{
    return $this->whereHas('user', function ($q) {
            $q->where('verified', 1)->whereNotNull('deleted_at');
        })->get();
}
```

Good:

```php
public function scopeActive($q)
{
    return $q->where('verified', true)->whereNotNull('deleted_at');
}

public function getActive(): Collection
{
    return $this->active()->get();
}

public function getArticles(): Collection
{
    return $this->whereHas('user', function ($q) {
            $q->active();
        })->get();
}
```

[🔝 Back to contents](#contents)

### **Prefer to use Eloquent over using Query Builder and raw SQL queries**

Bad:

```sql
SELECT *
FROM `articles`
WHERE EXISTS (SELECT * FROM `users` WHERE `articles`.`user_id` = `users`.`id` ...)
AND `verified` = '1' AND `active` = '1'
ORDER BY `created_at` DESC
```

Good:

```php
Article::has('user.profile')->verified()->latest()->get();
```

[🔝 Back to contents](#contents)

### **Mass assignment**

Bad:

```php
$article = new Article;
$article->title = $request->title;
$article->content = $request->content;
$article->verified = $request->verified;
$article->category_id = $category->id;
$article->save();
```

Good:

```php
$category->article()->create($request->validated());
```

[🔝 Back to contents](#contents)

### **Do not execute queries in Blade templates and use eager loading (N + 1 problem)**

Bad (100 users = 101 DB queries):

```blade
@foreach (User::all() as $user)
    {{ $user->profile->name }}
@endforeach
```

Good (100 users = 2 DB queries):

```php
$users = User::with('profile')->get();

@foreach ($users as $user)
    {{ $user->profile->name }}
@endforeach
```

[🔝 Back to contents](#contents)

### **Chunk data for data-heavy tasks**

Bad:

```php
$users = $this->get();
foreach ($users as $user) { ... }
```

Good:

```php
$this->chunk(500, function ($users) {
    foreach ($users as $user) { ... }
});
```

[🔝 Back to contents](#contents)

### **Prefer descriptive method and variable names over comments**

Bad:

```php
// Determine if there are any joins
if (count((array) $builder->getQuery()->joins) > 0)
```

Good:

```php
if ($this->hasJoins())
```

[🔝 Back to contents](#contents)

### **Do not put JS and CSS in Blade templates and do not put any HTML in PHP classes**

Use `@json` or data attributes instead of inline JS.

[🔝 Back to contents](#contents)

### **Use config and language files, constants instead of text in code**

Bad:

```php
return $article->type === 'normal';
return back()->with('message', 'Your article has been added!');
```

Good:

```php
return $article->type === Article::TYPE_NORMAL;
return back()->with('message', __('app.article_added'));
```

[🔝 Back to contents](#contents)

### **Use standard Laravel tools accepted by community**

| Task | Standard tools | 3rd party tools |
|------|---------------|-----------------|
| Authorization | Policies | Entrust, Sentinel |
| Compiling assets | Laravel Mix, Vite | Grunt, Gulp |
| Dev Environment | Laravel Sail, Homestead | Docker |
| Deployment | Laravel Forge | Deployer |
| Unit testing | PHPUnit, Mockery | Phpspec, Pest |
| Browser testing | Laravel Dusk | Codeception |
| DB | Eloquent | SQL, Doctrine |
| Templates | Blade | Twig |
| Form validation | Request classes | 3rd party packages |
| Authentication | Built-in | 3rd party solutions |
| API auth | Laravel Passport, Sanctum | 3rd party JWT/OAuth |
| Creating API | Built-in | Dingo API |
| DB structure | Migrations | Direct DB manipulation |
| Localization | Built-in | 3rd party packages |
| Testing data | Seeders, Factories, Faker | Manual creation |
| Task scheduling | Laravel Task Scheduler | 3rd party packages |

[🔝 Back to contents](#contents)

### **Follow Laravel naming conventions**

| What | How | Good | Bad |
|------|-----|------|-----|
| Controller | singular | ArticleController | ArticlesController |
| Route | plural | articles/1 | article/1 |
| Route name | snake_case with dots | users.show_active | users.show-active |
| Model | singular | User | Users |
| hasOne/belongsTo | singular | articleComment | articleComments |
| Other relationships | plural | articleComments | articleComment |
| Table | plural | article_comments | article_comment |
| Pivot table | singular alphabetical | article_user | user_article |
| Column | snake_case | meta_title | MetaTitle |
| Foreign key | singular + _id | article_id | ArticleId |
| Primary key | — | id | custom_id |
| Migration | — | YYYY_MM_DD_create_articles_table | articles |
| Method | camelCase | getAll | get_all |
| Variable | camelCase | $articlesWithAuthor | $articles_with_author |
| Collection | descriptive, plural | $activeUsers | $data |
| Object | descriptive, singular | $activeUser | $users |
| View | kebab-case | show-filtered.blade.php | showFiltered.blade.php |
| Config | snake_case | google_calendar.php | googleCalendar.php |
| Trait | adjective | Notifiable | NotificationTrait |
| Enum | singular | UserType | UserTypes |
| FormRequest | singular | UpdateUserRequest | UserFormRequest |
| Seeder | singular | UserSeeder | UsersSeeder |

[🔝 Back to contents](#contents)

### **Convention over configuration**

Follow Laravel conventions to avoid explicit configuration.

Bad:

```php
class Customer extends Model
{
    protected $table = 'Customer';
    protected $primaryKey = 'customer_id';
    // ...
}
```

Good:

```php
class Customer extends Model
{
    // Table 'customers', PK 'id' by convention
}
```

[🔝 Back to contents](#contents)

### **Use shorter and more readable syntax where possible**

| Common syntax | Shorter syntax |
|---------------|---------------|
| `Session::get('cart')` | `session('cart')` |
| `$request->input('name')` | `$request->name` |
| `return Redirect::back()` | `return back()` |
| `is_null($obj->rel) ? null : $obj->rel->id` | `optional($obj->rel)->id` |
| `Carbon::now()` | `now()` |
| `->orderBy('created_at', 'desc')` | `->latest()` |
| `->orderBy('created_at', 'asc')` | `->oldest()` |
| `->select('id', 'name')->get()` | `->get(['id', 'name'])` |

[🔝 Back to contents](#contents)

### **Use IoC / Service container instead of new Class**

Bad:

```php
$user = new User;
$user->create($request->validated());
```

Good:

```php
public function __construct(protected User $user) {}
$this->user->create($request->validated());
```

[🔝 Back to contents](#contents)

### **Do not get data from the `.env` file directly**

Bad: `$apiKey = env('API_KEY');`

Good:

```php
// config/api.php
'key' => env('API_KEY'),

// Usage
$apiKey = config('api.key');
```

[🔝 Back to contents](#contents)

### **Store dates in the standard format. Use accessors and mutators**

Bad:

```php
{{ Carbon::createFromFormat('Y-d-m H-i', $object->ordered_at)->toDateString() }}
```

Good:

```php
// Model
protected $casts = ['ordered_at' => 'datetime'];

// Blade
{{ $object->ordered_at->toDateString() }}
```

[🔝 Back to contents](#contents)

### **Do not use DocBlocks**

Use descriptive method names and return type hints instead.

Bad:

```php
/**
 * Checks if given string is a valid ASCII string
 * @param string $string
 * @return bool
 */
public function checkString($string) { }
```

Good:

```php
public function isValidAsciiString(string $string): bool { }
```

[🔝 Back to contents](#contents)

### **Other good practices**

- Avoid patterns alien to Laravel (RoR, Django, Symfony approaches)
- Never put logic in route files
- Minimize vanilla PHP in Blade templates
- Use in-memory DB for testing
- Don't override standard framework features
- Use modern PHP syntax while maintaining readability
- Avoid View Composers unless you really need them
