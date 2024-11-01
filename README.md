** This README is currently being worked on, please be patient **

# WP_Twig

This WordPress plugin allows you to use the [Twig template engine](http://twig.sensiolabs.org) in your WordPress themes. It is really simple to use and even includes a template wrapper enabling you to use a master layout file to keep your themes D.R.Y.

## Installation

Just install the plugin directly in the WordPress admim section or download it from here and place the entire folder in your WordPress plugin directory.

### Configuration

We need to specify two things before we're ready to use this plugin. We do this in the admin section of the plugin.

#### We need to make sure that...
1. 	**...we have access to a Twig installation:**
If you are using autoload in your project you probably won't have to do anything at all. But if you're not, make sure to point the plugin to the Twig ```Autoloader.php```
2. 	**...we have specified a template path (where we will place our Twig templates):**
If we do NOT specify a path the plugin will try to create a folder named 'twig' in your active theme folder (if it does'nt allready exist). You can specify multiple folders or even add seleral paths later on the fly (more on that later).

## Usage

Let's start with the most simple use of this plugin to get you going:

```php
<?php // index.php

Twig::View();
```
And that's it! The plugin will automatically know that you are in the index.php file, and try to find the template index.twig in any of the specified template folders.
We will later see how we can use this principle in a very creative manner.

Of course we can specify wich template to use, we do this without the file extension like so:

```php

Twig::View([
	'template' => 'post'
]);
```
The plugin will now look for the template post.twig.

#### Template folder structure
You can offcourse keep all your templates organized into folders and render them like so:

```php

Twig::View([
	'template' => 'components/header'
]);

Twig::View([
	'template' => 'index'
]);

Twig::View([
	'template' => 'components/footer'
]);
```
*If you find this an interesting aproach please read about template wrapping below.*

#### Passing variables to templates
To pass variables to our templates you pass the view function a context parameter. The context parameter is an array of variables of your choice:

```php

// define variables to pass on to template
$name = 'Martin';
$age = 26;

$footer_context = [
	'author' => $name,
	'date' => date('Y-m-d')
];

Twig::View([

	'template' => 'components/header',

	// pass on variables to use in template
	'context' => [
		'title' => 'Twig'
	]
]);

Twig::View([

	'template' => 'index',

	// pass on variables to use in template
	'context' => [
		'name' => $name,
		'age' => $age
	]
]);

Twig::View([

	'template' => 'components/header',

	// pass on variables to use in template
	'context' => $footer_context
]);
```
The variables we passed on are now available in the templates like so:

```twig
{# index.twig #}
<p>My name is {{ name }} and I am {{ age }} years old!</p>
```
This clearly shows how we can use the WordPress template files (ex. **index.php**) to handle all our logic and let the [Twig template engine](http://twig.sensiolabs.org) do what it does best.

### Remember Twig

Don't forget that you are using Twig. You can still use the awesome features of Twig such as: multiple inheritance, blocks, automatic output-escaping etc.

### Template hierarchy

This plugin utilizes WordPress template hierarchy in a very neat whay. Ex:
```php Twig::View( ['template' =>'page-about'] ); ```
The plugin will try to find the template **page-about.twig** and gracefully fall back to **page.twig** if it does'nt exist. It will actually fall back like this:
```page-about-me.twig -> page-about.twig -> page.twig```. Which will be very handy when we use master layout files.

### Template wrapping

The plugin comes with a simple template wrapper. It allows you to specify a master layout file: **_layout.php** and exposes two new functions:
```php

/**
 * returns the WordPress template being rendered
 * 
 * Ex: index, page, page-about
 */
get_twig_template();

/**
 * returns the absolute path of the same template
 *
 * Ex: ABSPATH/wp-content/themes/active-theme/index.php
 */
get_twig_template_path();
```

If we've enabled this feature in the admin section we can now create the file **_layout.php** in our theme folder and it might look like this:

```php
<?php // _layout.php

get_header( get_twig_template() );

include get_twig_template_path();

get_footer( get_twig_template() );
```
WordPress will now attempt to include **header.php**, the WordPress template (ex. **index.php**) and the **footer.php**.
Notice that doing this allows us to make **the get_header()** and **get_footer()** calls only once. We can now focus on the contents of our WordPress template files, and the header and footer files will be automatically included.
Because of the two new functions, if we have a file called **header-page.php** in our theme. It will be included when we visit a WordPress 'page'.

#### Template wrapper hierarchy

The (second) best thing about the template wrapper is that it also follows the template hierarchy. Thus we can create the file: **_layout-page.php** and it will we used when visiting a WordPress 'page'. We can even create the file: **_layout-page-about.php** and it will be used on our about page.
