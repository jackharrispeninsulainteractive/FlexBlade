# FlexBlade

[![PHP Tests](https://github.com/jackharrispeninsulainteractive/Lucent-Blade/actions/workflows/tests.yaml/badge.svg)](https://github.com/jackharrispeninsulainteractive/Lucent-Blade/actions/workflows/tests.yaml)
[![Build and Release](https://github.com/jackharrispeninsulainteractive/Lucent-Blade/actions/workflows/main.yaml/badge.svg)](https://github.com/jackharrispeninsulainteractive/Lucent-Blade/actions/workflows/main.yaml)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A **lightweight, standalone** Blade-inspired templating engine for **any PHP project**. FlexBlade brings the elegant syntax of Blade templating to your applications without requiring Laravel or any specific framework.

## ✨ Why FlexBlade?

- **🚀 Drop-in Ready**: Single PHAR file - just include and start templating
- **🔧 Framework Agnostic**: Works with any PHP project, framework, or vanilla PHP
- **🎨 Familiar Syntax**: Blade-style templating you already know and love
- **⚡ Zero Dependencies**: No Composer packages required for runtime
- **📦 Lightweight**: Complete templating engine in one small file
- **🔄 Smart Features**: Auto-minification, ViewBag, and component system built-in

## 🚀 Quick Start (60 seconds)

### 1. Download & Include

```bash
# Download the latest release
curl -L -o flexblade.phar https://github.com/jackharrispeninsulainteractive/FlexBlade/releases/latest/download/flexblade.phar
```

```php
<?php
// In your PHP file
define('VIEWS', __DIR__ . '/views/');
require_once 'flexblade.phar';

use FlexBlade\Blade\BladeCompiler;

$blade = new BladeCompiler();
echo $blade->render('welcome', ['name' => 'World']);
```

### 2. Create Your First Template

**File: `views/welcome.blade.php`**
```blade
<!DOCTYPE html>
<html>
<head>
    <title>Hello FlexBlade!</title>
</head>
<body>
    <h1>Hello, {{ $name }}!</h1>
    <p>FlexBlade is working perfectly!</p>
</body>
</html>
```

### 3. That's It!
Your template is now rendering with FlexBlade. No framework setup, no complex configuration - just pure templating power.

## 🎯 Use Cases

FlexBlade is perfect for:

- **🌐 Standalone Web Apps**: Add templating to any PHP website
- **🔧 Legacy Projects**: Modernize old PHP applications with Blade syntax
- **📊 Report Generation**: Create dynamic HTML reports and documents
- **📧 Email Templates**: Build beautiful email templates with data binding
- **🚀 Microservices**: Lightweight templating for service responses
- **🎨 Theme Systems**: Power custom themes and layouts
- **📱 Mobile Backends**: Generate mobile-friendly HTML responses

## 💡 Real-World Examples

### Simple Website Page
```php
// index.php
require_once 'flexblade.phar';
use FlexBlade\Blade\BladeCompiler;

$blade = new BladeCompiler();
$users = getUsersFromDatabase(); // Your data source

echo $blade->render('users', [
    'title' => 'User Directory',
    'users' => $users,
    'total' => count($users)
]);
```

```blade
<!-- views/users.blade.php -->
@extends('layouts.app')

<div class="container">
    <h1>{{ $title }}</h1>
    <p>Total users: {{ $total }}</p>
    
    @foreach($users as $user)
        <div class="user-card">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email ?? 'No email provided' }}</p>
        </div>
    @endforeach
</div>
```

### Email Template Generation
```php
// email-sender.php
use FlexBlade\Blade\BladeCompiler;

function sendWelcomeEmail($user) {
    $blade = new BladeCompiler();
    
    $htmlContent = $blade->render('emails.welcome', [
        'user' => $user,
        'company' => 'Your Company',
        'loginUrl' => 'https://yoursite.com/login'
    ]);
    
    // Send email with your preferred method
    mail($user->email, 'Welcome!', $htmlContent, [
        'Content-Type' => 'text/html'
    ]);
}
```

### Report Generation
```php
// reports.php
use FlexBlade\Blade\BladeCompiler;

function generateMonthlyReport($data) {
    $blade = new BladeCompiler();
    
    $reportHtml = $blade->render('reports.monthly', [
        'month' => date('F Y'),
        'sales' => $data['sales'],
        'customers' => $data['customers'],
        'revenue' => $data['revenue']
    ]);
    
    // Output as PDF, save to file, or display
    return $reportHtml;
}
```

## 🎨 Template Features

### ✅ Variables & Expressions
```blade
<!-- Basic variables -->
<h1>{{ $title }}</h1>
<p>Welcome, {{ $user->name }}!</p>

<!-- Null coalescing -->
<span>{{ $user->nickname ?? 'Guest' }}</span>

<!-- Conditional checks -->
@if(isset($user))
    <p>User is logged in</p>
@endif
```

### ✅ Loops & Data
```blade
<!-- Simple foreach -->
@foreach($items as $item)
    <li>{{ $item->title }}</li>
@endforeach

<!-- With keys -->
@foreach($data as $key => $value)
    <strong>{{ $key }}:</strong> {{ $value }}<br>
@endforeach
```

### ✅ Components & Reusability
```blade
<!-- Self-closing components -->
<x-alert type="success" message="Data saved!" />

<!-- Components with content -->
<x-card title="User Profile">
    <p>{{ $user->bio }}</p>
    <x-button>Edit Profile</x-button>
</x-card>
```

**Component File: `views/Blade/Components/alert.blade.php`**
```blade
<div class="alert alert-{{ $type }}" role="alert">
    {{ $message ?? $children }}
</div>
```

### ✅ Layout Inheritance
```blade
<!-- Layout: views/Blade/layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>{{ $view->title ?? 'My App' }}</title>
</head>
<body>
    <nav><!-- Your navigation --></nav>
    <main>
        @yield('content')
    </main>
    <footer><!-- Your footer --></footer>
</body>
</html>
```

```blade
<!-- Page: views/dashboard.blade.php -->
@extends('layouts.app')

<h1>Dashboard</h1>
<p>Welcome to your dashboard!</p>
```

### ✅ ViewBag for Shared Data
```php
// Set global data accessible in all templates
use FlexBlade\View;

View::Bag()->put('siteName', 'My Awesome Site');
View::Bag()->put('user', $currentUser);
View::Bag()->putArray([
    'theme' => 'dark',
    'version' => '2.1.0'
]);
```

```blade
<!-- Access in any template -->
<title>{{ $view->siteName }}</title>
<p>Version: {{ $view->version }}</p>
```

## 🔧 Framework Integration Examples

### Vanilla PHP
```php
<?php
require_once 'flexblade.phar';
use FlexBlade\Blade\BladeCompiler;

$blade = new BladeCompiler();
echo $blade->render('page', $data);
?>
```

### With Slim Framework
```php
$app->get('/users', function ($request, $response) {
    $blade = new FlexBlade\Blade\BladeCompiler();
    $html = $blade->render('users', ['users' => getAllUsers()]);
    $response->getBody()->write($html);
    return $response;
});
```

### With CodeIgniter
```php
// In your controller
public function index() {
    $blade = new FlexBlade\Blade\BladeCompiler();
    $data = ['users' => $this->user_model->get_all()];
    echo $blade->render('user-list', $data);
}
```

## ⚡ Built-in Performance Features

### Auto-Minification
FlexBlade automatically minifies your HTML, CSS, and JavaScript:

```blade
<style>
    .button {
        background-color: #007cba;
        padding: 10px 20px;
        border-radius: 4px;
    }
</style>

<script>
    function showAlert() {
        alert('Hello World!');
    }
</script>
```

Output is automatically compressed without any configuration needed.

### Smart Asset Handling
```blade
<!-- Prevent duplicate CSS/JS with data-minify-once -->
<style data-minify-once="true">
    .shared-styles { color: red; }
</style>

<!-- Group related styles -->
<style data-minify-scope="forms">
    .form-input { padding: 5px; }
</style>
```

## 📁 Recommended Directory Structure

```
your-project/
├── views/
│   ├── Blade/
│   │   ├── Components/
│   │   │   ├── alert.blade.php
│   │   │   ├── button.blade.php
│   │   │   └── card.blade.php
│   │   └── layouts/
│   │       ├── app.blade.php
│   │       └── email.blade.php
│   ├── welcome.blade.php
│   ├── users.blade.php
│   └── dashboard.blade.php
├── public/
│   └── index.php
└── flexblade.phar
```

## 🔧 Requirements

- **PHP**: 8.4 or higher
- **Extensions**: `phar`, `dom`, `mbstring`, `fileinfo`
- **Storage**: Write access for template caching (optional)

## 🚀 Installation Options

### Option 1: Direct Download
```bash
curl -L -o flexblade.phar https://github.com/your-repo/releases/latest/download/flexblade.phar
```

### Option 2: Build from Source
```bash
git clone https://github.com/your-repo/flexblade.git
cd flexblade
php build.php
```

## 🤝 Contributing

We welcome contributions! FlexBlade is designed to be:

- **Simple**: Easy to understand and modify
- **Fast**: Minimal overhead and quick rendering
- **Compatible**: Works with any PHP setup

### Development Setup
```bash
git clone https://github.com/your-repo/flexblade.git
cd flexblade
composer install
php vendor/bin/phpunit
```

## 📝 License

FlexBlade is open-sourced software licensed under the [MIT license](LICENSE).

## 📊 Laravel Blade Compatibility

FlexBlade implements a subset of Laravel Blade functionality, focusing on the most commonly used features while maintaining simplicity and performance.

### ✅ **Supported Features**

**Core Templating:**
- `{{ $variable }}` - Variable output with automatic escaping
- `{{ $variable ?? 'default' }}` - Null coalescing operator
- `{{ $object->property }}` - Object property access
- `{{ $view->property }}` - ViewBag access (FlexBlade extension)

**Control Structures:**
- `@if($condition) ... @endif` - Conditional blocks
- `@foreach($items as $item) ... @endforeach` - Basic foreach loops
- `@foreach($items as $key => $value) ... @endforeach` - Foreach with keys
- `isset($variable)` - Variable existence checks

**Template Organization:**
- `@extends('layout')` - Layout inheritance
- `@yield('section')` - Content sections
- `@include('partial')` - Include other templates
- `@php ... @endphp` - PHP code blocks
- `@use('Class')` - Use statements

**Components:**
- `<x-component />` - Self-closing components
- `<x-component>content</x-component>` - Components with content
- Component attributes and props
- `$children` variable for component content

### ❌ **Not Supported**

**Advanced Control Structures:**
- `@else` / `@elseif` - Use nested `@if` statements instead
- `@unless` - Use `@if` with negation instead
- `@while` / `@for` loops - Use `@foreach` or `@php` blocks
- `@switch` / `@case` - Use `@if` chains or `@php` blocks

**Sections & Stacks:**
- `@section` / `@endsection` - Use `@extends` / `@yield` pattern
- `@push` / `@stack` - Use direct includes instead
- `@prepend` - Not available

**Authentication & Authorization:**
- `@auth` / `@guest` - Handle authentication in controllers
- `@can` / `@cannot` - Handle authorization in controllers

**Laravel-Specific Features:**
- `{!! $html !!}` - Raw output (all output is escaped in FlexBlade)
- `@csrf` - Handle CSRF in your framework
- `@method` - Handle HTTP methods in your framework
- `@error` / `@enderror` - Handle validation in controllers
- `@json()` - Use `json_encode()` in `@php` blocks
- `@dump()` / `@dd()` - Use `var_dump()` in `@php` blocks

**Advanced Components:**
- Component classes - Only anonymous components supported
- Slots (`@slot`) - Use component attributes instead
- `@props` directive - Use direct variable access

### 🔄 **Migration Tips**

**From Laravel Blade:**
1. Replace `@else` with nested `@if` statements
2. Use `@php` blocks for complex logic
3. Handle authentication/authorization in controllers
4. Use HTML comments instead of Blade comments
5. Convert sections to includes or direct content

**Example Migration:**
```blade
<!-- Laravel Blade -->
@if($user)
    <p>Welcome, {{ $user->name }}</p>
@else
    <p>Please log in</p>
@endif

<!-- FlexBlade -->
@if(isset($user))
    <p>Welcome, {{ $user->name }}</p>
@endif
@if(!isset($user))
    <p>Please log in</p>
@endif
```

## 🙏 Credits

- Inspired by Laravel Blade templating
- Built for the modern PHP ecosystem
- Maintained by developers who believe templating should be simple

---

<div align="center">
  <strong>Made with ❤️ for the PHP Community</strong><br>
  <em>Bringing elegant templating to every PHP project</em>
</div>
