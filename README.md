# LucentBlade

[![PHP Tests](https://github.com/jackharris/lucent-blade/actions/workflows/tests.yml/badge.svg)](https://github.com/jackharris/lucent-blade/actions/workflows/tests.yml)
[![Build and Release](https://github.com/jackharris/lucent-blade/actions/workflows/build-release.yml/badge.svg)](https://github.com/jackharris/lucent-blade/actions/workflows/build-release.yml)
[![Latest Release](https://img.shields.io/github/v/release/jackharris/lucent-blade)](https://github.com/jackharris/lucent-blade/releases/latest)
[![PHP Version](https://img.shields.io/badge/php-8.4%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A Blade-inspired templating engine designed specifically for the Lucent Framework. While drawing inspiration from Laravel Blade, LucentBlade implements its own syntax and features tailored for Lucent applications.

## ✨ Features

- **🎨 Blade-Style Syntax**: Familiar templating with `{{ }}` variables and `@` directives
- **🔧 Component System**: Reusable components with `<x-component>` syntax
- **📄 Layout Inheritance**: Template extension with `@extends` and `@yield`
- **⚡ Auto-Minification**: Built-in HTML, CSS, and JavaScript minification
- **🔄 ViewBag Integration**: Access shared data with `$view->property` syntax
- **🚀 Easy Integration**: Drop-in package for Lucent Framework
- **📦 Self-Contained**: Distributed as a single PHAR file

## 🚀 Quick Start

### Installation

Download the latest release:

```bash
curl -L -o lucent-blade.phar https://github.com/jackharris/lucent-blade/releases/latest/download/lucent-blade.phar
```

Include in your Lucent project:

```php
// In your bootstrap file
define('VIEWS', __DIR__ . '/App/Views/');
require_once 'path/to/lucent-blade.phar';
```

### Basic Usage

#### Controller Example:

```php
use LucentBlade\BladeResponse;

class HomeController
{
    public function index(): BladeResponse
    {
        return new BladeResponse('welcome', [
            'title' => 'Welcome to Lucent',
            'user' => $this->getUser()
        ]);
    }
}
```

#### Template Example (`App/Views/welcome.blade.php`):

```blade
@extends('layouts.app')

<div class="hero">
    <h1>{{ $title }}</h1>
    <p>Hello, {{ $user->name ?? 'Guest' }}!</p>
    
    <x-alert type="success">
        Welcome to your Lucent application!
    </x-alert>
</div>

@if(isset($user))
    <div class="user-dashboard">
        <h2>Your Dashboard</h2>
        @foreach($user->notifications as $notification)
            <div class="notification">{{ $notification->message }}</div>
        @endforeach
    </div>
@endif
```

## 🎯 Supported Syntax

### ✅ Core Features
- **Variables**: `{{ $variable }}`, `{{ $user->name }}`
- **Null Coalescing**: `{{ $variable ?? 'default' }}`
- **ViewBag Access**: `{{ $view->property }}`
- **Comments**: `{{-- This is a comment --}}`

### ✅ Control Structures
- **If Statements**: `@if($condition) ... @endif`
- **Foreach Loops**: `@foreach($items as $item) ... @endforeach`
- **Foreach with Keys**: `@foreach($items as $key => $value) ... @endforeach`
- **Isset Checks**: `isset($variable)`

### ✅ Template Organization
- **Layout Inheritance**: `@extends('layouts.app')`
- **Content Sections**: `@yield('content')`
- **Includes**: `@include('partials.header')`
- **PHP Blocks**: `@php $total = $items->sum(); @endphp`
- **Use Statements**: `@use('App\Models\User')`

### ✅ Components
- **Self-Closing**: `<x-alert type="success" message="Done!" />`
- **With Content**: `<x-card title="User"><p>Content here</p></x-card>`

### ❌ Not Supported (Unlike Laravel Blade)
- `@else`, `@elseif` - use nested `@if` statements
- `@unless` - use `@if` with negation
- `@while`, `@for` loops - use `@foreach` or `@php` blocks
- `@section/@endsection` - use `@extends/@yield` pattern
- Raw output `{!! !!}` - all output is escaped
- `@auth`, `@guest`, `@can` - handle in controllers
- `@push/@stack` - use direct includes
- `@csrf`, `@method` - handle in Lucent framework

## 📁 Directory Structure

```
your-project/
├── App/
│   └── Views/
│       ├── Blade/
│       │   ├── Components/
│       │   │   ├── alert.blade.php
│       │   │   └── card.blade.php
│       │   └── layouts/
│       │       └── app.blade.php
│       ├── welcome.blade.php
│       └── home.blade.php
└── packages/
    └── lucent-blade.phar
```

## 🔧 Component Example

**Create** `App/Views/Blade/Components/alert.blade.php`:

```blade
<div class="alert alert-{{ $type ?? 'info' }}" style="padding: 15px; margin: 10px 0; border-radius: 4px;">
    {{ $message ?? $children }}
</div>
```

**Use in templates**:

```blade
<x-alert type="success" message="Operation completed!" />

<x-alert type="error">
    Something went wrong!
</x-alert>
```

## 🏗️ Layout Example

**Layout** `App/Views/Blade/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $view->title ?? 'LucentBlade App' }}</title>
</head>
<body>
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
</body>
</html>
```

**Page Template**:

```blade
@extends('layouts.app')

<h1>{{ $title }}</h1>
<p>Page content goes here</p>
```

## 🔧 Requirements

- **PHP**: 8.1 or higher
- **Lucent Framework**: Any version
- **Extensions**: `phar`, `dom`, `mbstring`, `fileinfo`

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes with tests
4. Submit a pull request

### Development Setup

```bash
git clone https://github.com/jackharris/lucent-blade.git
cd lucent-blade
composer install
php vendor/bin/phpunit --bootstrap dev_build.php --configuration phpunit.xml
```

## 📝 License

LucentBlade is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Inspired by Laravel Blade templating engine
- Built specifically for the Lucent Framework
- Thanks to all contributors

---

<div align="center">
  <strong>Made with ❤️ for the Lucent Framework</strong>
</div>