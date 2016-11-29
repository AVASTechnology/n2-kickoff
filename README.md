# n2-kickoff - BETA
Angular2 Project Generator wrote in PHP

# Requirements
Should work on php 5.6+. Testing was all done on php 7.0 but there are no features specific to php 7 used.
-SimpleXML
-Phar

# Quickstart
Currently the generator only works from the command line. This is intentional because most data validation checks required to integrate into a web app are skipped. The current build is assuming you are running it in a dev environment where you created the xml template file.

Simplest construction and packaging from a template file.

    php ng2-kickoff.php -f TEMPLATE.xml


This will create the basic Angular2 app files and directory structure then package them all together in a nice tar.gz file.

The Official Angular2 Quickstart example template is 'demo/tour-of-heroes.xml' and generates all the files created by the end of the general quickstart guide.

# Command Line Options

- -f                     Template file to use for package creation
- -n  --new              Create a new package, overwriting any current package that may be present
- -v  --version          Package versioning
- -s  --skip-archiving   Skip creatign the package archive (.tar.gz) file
- --packagedir           Directory for creating the package file. Defaults to 'package'




# Template File Formatting
Template files are XML and relatively simple in their constuction. The details about the project framework you want create with a little bit of built in intelligence about dependencies.

## XML Elements

### ng2project
The ng2project element is the basic wrapper element for all the content.

### package
The package element contains all the core details about the 

#### @attributes
- name: Name of the project. This should be alphanumeric and may contain dashes (-) and underscores (_). Avoid other characters since the name is used to create various file names.
- version: Version of this project. Should be in numerical sequence-based version (ie 1.2.3) to allow for parsing by the php version parser.

Example:

    <package name="tour-of-heroes" version="1.0">
  
#### title
The content is the title of the package.

Example:

    <title>Angular Quickstart Tour of Heroes</title>

#### description
The content is the description of the package.

Example:

    <description>This is a demostration of using ng2Kickoff using the files created during the official tutorial.</description>

#### copyright
The content is the copyright of the package.

Example:

    <copyright>Anthony Green, AVAS Technology and Google, Inc</copyright>

#### license
The content is the name of the license for the package.

Example:

    <license>MIT</license>



### module
The module element contains the bulk of the information about the app and how it is created. The module elements in the template act in a simlar manner to the modules of Angular2, consequently can be embeded within each other (via the children element).

#### @attributes
- name: Name of the module. This should be alphanumeric and may contain dashes (-) and underscores (_). Avoid other characters since the name is used to create various file names.
- selector: HTML Element Selector used to idenitfy this module. This should be alphanumeric and mpay contain dashes (-). Avoid other characters since this value creates the html element embeded into the html template file.
- styles: Use a styles directory (/css) for organizing all the css files. Should be set to either "true" or "false".
- templates: Use a templates directory (/templates) for organizing all the html template files. Should be set to either "true" or "false".

#### traits
Container for module trait elements. 

#### trait
Traits (or mixins) to apply to the elements of the module. This allows for easy reuse of code blocks, like for common accessor and error handling functions. 

##### @attributes
- name: Name of the trait.

#### routes
Container for module route elements. 

#### route
Short tags describing the routing paths. 

##### @attributes
- path: URL path for the route.
- redirectTo: URL path for redirecting this path.
- component: Component name / class name.

#### components
Container for module component elements. 

#### component
The component element contains all the details relevant to creating an individual component within the module.

##### @attributes
- name: Name of the component
- selector: Selector name of the component.
- bootstrap: Bootstrap this component into the module. Set to "true" to bootstrap.

#### providers
Container for module service elements. 

#### service
The service element contains all the details relevant to creating an individual service within the module. The content of the service element contains all the class declarations relevant to creating this service.

##### @attributes
- name: Name of the component

#### dataobjects
Container for module data object elements. 

#### object
The dataobject element contains the declarations for a class used simply to manage the transportation of data through out the application. Content are class declarations.

##### @attributes
- name: Name of the class

### Class Declarations
Class declarations are elements used to create a declaration within a ts class. These elements are used to create the class properties and methods declared in the class. The effect of the content of the element is dependent upon the 'type' attribute.

##### @attributes
- type: Type of declaration
- - string: Simple string declaration to a class property. If the element's content is omitted, the property is typecast as a string.
- - number: Simple number declaration to a class property. If the element's content is omitted, the property is typecast as a number.
- - function: Declaration is a class method. Content of the element corresponds to the content of the method
- access: TS access level of the declaration (private, protected, public). @optional @default[public]
- args: Arguments to apply for function declarations
- return: Return type for typecasting
- return-generic: Return type is a genericized version of this type. For example return="Promise" and return-generic="Hero" yeilds 'Promise<Hero>'


