*PACKAGE IS NOT MAINTAINED, USE AT YOUR OWN RISK*

# SolbegLaravelVueValidation

It is plugin for [Laravel](https://laravel.com/) applications
that use [Vee-Validate](http://vee-validate.logaretm.com/) plugin for front validation.

It automatically converts laravel's [FormRequest](https://laravel.com/docs/5.3/validation#form-request-validation)
rules to [Vee rules](http://vee-validate.logaretm.com/rules#syntax).
It also passes errors messages to Vee validator.

So you may write your rules once in laravel application,
so the same will be used in front side too.

## Requirements

- PHP >= 5.6.4
- Laravel Framework 5.3.*
- [Vue 1.x or 2.x](https://vuejs.org/)
- [Vee-Validate Vue plugin 1.x or 2.x](http://vee-validate.logaretm.com/#about)

## Installation

Install plugin using [composer](http://getcomposer.org):

```
$ php composer.phar require solbeg/laravel-vue-validation
```

After you have installed this vendor, add the following lines.

In your Laravel config file `config/app.php` in the `providers` array
add the service provider for this package.

```php
    // ...
    Solbeg\VueValidation\ServiceProvider::class,
    // ...
```

And add the facade of this package to the `aliases` array.

```php
    // ...
    'F' => Solbeg\VueValidation\Facades\Form::class,
    'HTML' => Solbeg\VueValidation\Facades\Html::class,
    // ...
```

Publish assets for this vendor:

```
$  php artisan vendor:publish --force --provider="Solbeg\VueValidation\ServiceProvider" --tag=public
```

Connect JS file for this plugin in your layout.
*Note!* This JS file must be included *after* Vue & Vee JS files:

```twig
<script src="{{ asset('vendor/solbeg/laravel-vue-validation/init-vue-validation.js') }}"></script>
```

Add the following JS code in your layout,
so the Vue will use this plugin for validating:

```js
Vue.use(SolbegLaravelVueValidation);
```

## Usage

In your blade template:

```twig

{{ F::open([
    // ...
    'request' => \App\Http\Requests\YourFormRequestClass::class,
]) }}

    ...

    {!! F::controlGroup('phone',
        F::label('phone', 'Phone number'),
        F::text('phone', old('phone', $user->phone))
    ) !!}


    ...

{{ F::close([
    /**
     * Additional params for Vue object.
     * They will be passed in `new Vue({here})` constructor
     */
    'data' => [
        'prop1' => 'val1',
    ],
]) }}

{{-- OR without additional Vue options --}}
{{ F::close() }}
{{ F::close(true) }}

{{-- OR `false` if you initialize Vue object by himself --}}
{{ F::close(false) }}

```

