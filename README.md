#Inputter 
A  Flexible Form Input Control

##Build Status

In Beta at the moment...check back soon!

##Version

The current version is considered Beta.  This means that it is *ready enough* to test and use, but beware that you should update frequently.

As this software is **BETA**, **Use at your own risk**!

## Changelog
Changelog can be found [here](https://github.com/prograhammer/inputter/blob/master/CHANGELOG.md)

## Table of contents

1. [Quick Feature Highlight](#quick-feature-highlight)
2. [Installation](#installation)
3. [Detailed Usage and Server-side Setup](#detailed-usage-and-server-side-setup)
  1. [Extend Inputter and Use](#usage-in-controllers)
  2. [Input Types and Public Properties](#types)
4. [Use with Laravel 5](#use-with-laravel-5)
  1. [Controller Dependency Injection & Validation](#controller-validation)
  2. [Blade Template Views](#views)
5. [Client-side Use](#child-form)
  1. [Script Setup](#declaration)
  2. [Basic Options](#options)
  3. [Ajax Options](#ajax)
  4. [HTML history and pushState](#push-state)
  5. [Cascading Inputs](#cascading)
6. [Roll Your Own](#roll-your-own)
7. [Issues and bug reporting](#issues-and-bug-reporting)
8. [License](#license)

#Quick Feature Highlight
* Clean usage:
 
        $exampleInput = new MyExampleInput;   
        $exampleInput->fill($_GET);     // <-- ie. fill it with data from GET request 
        $viewData = $exampleInput->render();    // <-- render it out as an array of HTML inputs for your View
        // and much more!
* Super clean usage in a Laravel 5 controller:

        public function index(MyExampleInput $exampleInput){   // <-- filled w/request data and validated automatically!
              return view('examples.index', $exampleInput->render());     
        } 

* Simplifies server-side representation of inputs. Also offers a wide-range of public properties for flexible configuration and control:
   
        // In a child class you can setup your inputs like this (read further into docs for more information...)

        $this->addField("state_id", "select")
             ->setValue("")
             ->setCascadeTo("city_id")
             ->setAttribs([
                   "style" => "width:100px;",
                   "class"=> "form-control"
               ])
             ->setOptions(function(){
                    // some logic here that returns all the choices appearing in the drop-down box
               });
        
        $this->addField("city_id", "select")
             ->setValue("")
             ...

* Simplifies client-side code:
   
        // Client-side jQuery as simple as this
        $(".my-example").inputter({
              // set some options here
        });  
 
* Client-side methods available to help you (and you don't need to rely on `<form>` tags):

        // Gather all the values from the inputs
        var data = $(".my-example").data("inputter").get();
        alert(data.state_id);   // <-- outputs the value of your state_id dropdown
        
       
* Be Secure and Ready for placement anywhere in Views/Templates and doesn't require any `<form>` tags (so you are free to place your inputs anywhere!):
	
        <table>
          <tr><td> State: </td><td> <?=$state;?> </td></tr>
          <tr><td> City:  </td><td> <?=$city;?>  </td></tr>
        </table>
			
* Handles HTML5 history/pushState and updating inputs in the browser address bar (without refreshing the page)

* Makes input cascading a breeze!

* Helps prevent HTML tag naming collisions (with built in prefixing and automatic stripping of prefixes)

        $prefix = "my-example";

* Good documentation with examples (keep reading)

* Includes several advanced input types like *AutoComplete*, *DateTimePicker*, *Chosen.js*, and more for you to use

* Or easily Roll-Your-Own custom input types

* Secure input with HTML encode/XSS protection

* Easy to add ajax loading animated-gifs/images to inputs (such as during cascading) or any target selectors

* Laravel 5 Ready (but without adding any additional bloat or dependencies, so feel free to use it stand-alone)

Continue reading documentation for details on these features and more...

#Installation

## Composer

Add a `composer.json` file to your project with the following:

    {
        "require": {
            "Prograhammer/Inputter": "dev-master"
        }
    }

Run `composer update`

## Laravel 5

Add Service provider to `config/app.php`

    'providers' => [
        // ...
        'Prograhammer\Inputter\InputterServiceProvider'
    ]

And Facade (also in `config/app.php`)

    'aliases' => [
        // ...
        'Inputter' => 'Prograhammer\Facades\Inputter'
    ]

# Detailed Usage and Server-side Setup

### Extend Inputter and Return Your Setup Array

Create a file somewhere in your app to hold a class that extends Inputter, implement the InputInterface, and add the `setInput()` method to return your setup array:

    // file: app/Http/Inputs/Examples/ExampleInput.php
    <?php namespace App\Http\Input\Examples

    use Prograhammer\Inputter
    use MyTableDataGateways\Queries as Queries;

    class ExampleInput extends Inputter implements InputInterface{
        
        public $prefix = "example-input";

        public function init()
        {
             $input = array();
              
             // Email
             $this->addField("email", "text")
                  ->setValue("")
                  ->setAttribs([
                       "style" => "width:100px;",
                       "class"=> "form-control",
                       "data-placeholder" => "enter your email",
                       "tabindex" => "0"
                    ]);

             // Password
             $this->addField("password", "password")
                  ->setValue("")
                  ->setAttribs([
                       "style" => "width:100px;",
                       "class"=> "form-control",
                       "tabindex" => "1"
                    ]);

             // Country
             $this->addField("cntry", "select")
                  ->setValue("")
                  ->setCascadeTo("state")
                  ->setAttribs([
                       "style" => "width:100px;",
                       "tabindex" => "2"
                    ])
                  ->setOptions(function(){
                        // Example of using an array
                        return [["text"=>"",       "id"=>"" ],
                                ["text"=>"USA",    "id"=>"1"],
                                ["text"=>"Canada", "id"=>"2"]];                         
                    });

             // State
             $this->addField("state", "select")
                  ->setValue("")
                  ->setCascadeTo("city")
                  ->setAttribs([
                       "style" => "width:100px;",
                       "tabindex" => "3"
                    ])
                  ->setOptions(function(){
                        // Example of performing some logic (a query) and returning array
                        
                        $data = array();
                        $query = new Queries\Inputs\States();

                        // Query for an array of states
                        $data = $query->findByCountry("states.name as 'text', states.id as 'id'", 
                                                           $this->input['cntry']->getValue());
                           
                        // Add a blank row for the top option of the dropdown											    
                        array_unshift($data, array("text"=>"All","value"=>""));
                         
                        return $data;                        
                    });

             // City
             $this->addField("city", "select")
                  ->setValue("")
                  ->setAttribs([
                       "style" => "width:100px;",
                       "tabindex" => "4"
                    ])
                  ->setOptions(function(){
                        // Example of performing some logic (a query) and returning array
                        
                        $data = array();
                        $query = new Queries\Inputs\Cities();

                        // Query for an array of cities
                        $data = $query->findByState("cities.name as 'text', cities.id as 'id'", 
                                                           $this->input['state']->getValue());
                           
                        // Add a blank row for the top option of the dropdown											    
                        array_unshift($data, array("text"=>"All","value"=>""));
                         
                        return $data;                        
                    });

               return $input;   
        }    



    }


### Input Types and Public Properties

Use the `InputFactory::make` method and it's public properties to setup your input.

    $input['email'] = InputFactory::make('text');   // <-- makes an input type "text"
    $input['email']->style = "width:100px;";    // <-- a property you can set for "text" inputs

Inputter comes with several ready-to-use **Input Types**. You can view each input type and its properties in the files found in `src/Prograhammer/Inputter/InputTypes/`:

   * Select (Select.php) - For generating `<select>` type dropdowns 
   * Text (Text.php) - For generating `<input type='text'>` text inputs
   * Password (Text.php) - For generating `<input type='password'>` inputs
   * Hidden (Text.php) - For generating `<input type='hidden'>` inputs
   * AutoComplete (AutoComplete.php) - For generating jqueryUI autoComplete inputs
   * Date (DateTime.php) - For generating jqueryUI datepicker (and datetimepicker) inputs
   * Links (Links.php) - For generating a set of links that act as inputs (ie. Alpha pagination)

# Use with Laravel 5

### Controller Dependency Injection & Validation

Update the file that contains your `setInput()` method with the changes shown below. Refer to the [Laravel 5 docs on validation](http://laravel.com/docs/5.0/validation#form-request-validation) to learn how to define rules and custom messages.

    // file: app/Http/Inputs/Examples/ExampleInput.php
    <?php namespace App\Http\Input\Examples

    use Prograhammer\Inputter;
    use Prograhammer\InputFactory;
    use Illuminate\Http\Request;                                 // <-- Add this
    use Illuminate\Contracts\Validation\ValidatesWhenResolved;   // <-- Add this

    class ExampleInput extends Inputter implements InputInterface, ValidatesWhenResolved{   // <-- Add this additional interface

        use \Prograhammer\Inputter\ValidatesRequestsTrait;     // <-- Add this trait
        
        public function __construct(Request $request){         // <-- Add a constructor
             parent::__construct();
             $this->request = $request;
             $this->updateValues($this->request->input());
        }

        public $prefix = "example-input";

        public function setInput()
        {
             $input = array();

             $input['email'] = InputFactory::make("text");
             $input['email']->value = "";
             $input['email']->style = "width:100px;";
             $input['email']->custom['data-placeholder'] = "enter your email";
             $input['email']->custom['tabindex'] = "0";
             $input['email']->rules = ['required','email'];           // <-- add some rules to see them validated
             $input['email']->messages = ['email.required' => 'Blah blah email is required'];  // <-- and custom error messages

             // ...

Now your input file is ready to be injected into your Laravel 5 Controller:

    public function index(MyExampleInput $exampleInput){    // <-- injected here, filled w/request data and validated
          return view('examples.index', $exampleInput->render());    // <-- send the inputs to a view
    } 

### Blade Template Views

In your blade template views, the inputs will already be html-escaped, so you don't need to do this in blade. Use the `{!!   !!}` notation to ensure the inputs are not doubly escaped.

    <table>
       <tr><td> State: </td><td> {!! $email; !!}     </td></tr>
       <tr><td> City:  </td><td> {!! $password; !!}  </td></tr>
    </table>

# Client-Side Use

### Script Setup

Inputter has abandoned the requirement to use `<form>` tags, freeing you to use the inputs however you like and wherever you like in your page design. You can still add a form tag for *progressive enhancement* reasons, and offer your JS users an enhanced ajax experience.

    <html>
    <body>
    <div>
        <table>
           <tr><td> State: </td><td> {!! $email; !!}     </td></tr>
           <tr><td> City:  </td><td> {!! $password; !!}  </td></tr>
        </table>

        <button id="example-input-submit">Submit</button>
    </div>

    <script type="text/javascript" src="{{ asset('/packages/prograhammer/inputter/inputter.js') }}"></script>

    <script>
        $(function() {

              // Setup Inputter, uses the prefix you set server-side, as a class selector here
              $(".example-input").inputter({
                     // some options can be set here
              });

              // Submit button
              $(".#example-input-submit").click(function(){

                    // Retrieve input values into an array
                    var data = $(".example-input").data("inputter").get();
                    data.additional = "some additional param";   // Easy to add any additional param/values to the array

                     // Make an ajax call
                     $.ajax({
                           type:       "POST",
                           url:        "{{ Request::url()  }}",
                           dataType:   "json",
                           data:       data,
                           success:    function(response){
                                alert('data successfully sent!');
                           }
                     });
              });

    </script>
    </body>
    </html>

### Todo

* Complete README.md documentation
* Perform building of inputs completely client-side (just send one big JSON setup string on first page load)
       
### License

Inputter is released under the MIT License.