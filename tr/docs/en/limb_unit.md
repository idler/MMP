# limb_unit — advanced SimpleTest tests runner
Documentation is actual for limb/TESTS_RUNNER-1.0.0-stable

limb/TESTS_RUNNER package that provides limb_unit **has no dependencies** on any other Limb3 packages

## Description
**limb_unit** is an advanced [SimpleTest](http://www.simpletest.org/) based tests runner utility for PHP5. In a nutshell, **limb_unit** finds and executes tests within the file system.

Main features:

* Can run single tests as well as tests under specified directory recursively
* Hierarchical tests fixtures
* Conditional tests execution
* Tests code coverage

## Installation
**limb_unit** is shipped with Limb3 TESTS_RUNNER package and can be installed via PEAR channel. Here's an example of quick installation:

    $ pear channel-discover pear.limb-project.com
    $ pear install limb/tests_runner-beta 

That's it! Try running in your console:

    $ limb_unit -h

If everything was installed properly you should see a help message:

    Usage:
      limb_unit [OPTIONS] <file|dir> [<file1|dir1>, ... <fileN|dirN>]
      Advanced SimpleTest unit tests runner. Finds and executes unit tests within filesystem.
    Arguments:
      <file|dir> [<file1|dir1>, ... <fileN|dirN>] - a list of files/directories, globs are supported(e.g. '*')
      KEY1=value1 [KEY2=value2, ... KEYN=valueN]  - a list of arbitrary key=value pairs which will be declared 
                                                as constants using PHP define call
    Options:
      -h, --help                        Displays this help and exit
      -c, --config=/file.php            PHP configuration file path
      -I, --include='filter1;filter2'   Sets file filters used for including test files during
                                        recursive traversal of directories.
                                        '*Test.class.php;*test.php;*Test.php' by default.
      -G, --groups=group1[,group2]      Comma separated list of test groups defined in annotations 
                                        tags which should be executed(e.g @group group1,group2) 
      -T, --tests=Foo[,Bar]             Comma separated list of test classes which should be
                                        executed 
      -M, --methods=testFoo[,testBar]   Comma separated list of test methods which should be
                                        executed 
      -C, --cover='path1;path2'         Sets paths delimitered with ';' which should be analyzed
                                        for test coverage(requires XDebug extension!)
      --cover-report=dir                Sets coverage report directory
      --cover-exclude='path1;path2'     Sets paths delimitered with ';' which should be excluded
                                        from coverage analysis

## Some quick limb_unit usage examples for the impatient

    $ limb_unit my_test.php      #running all test cases defined in my_test.php
    $ limb_unit *_test.php       #running all test cases contained in *_test.php files
    $ limb_unit tests            #running recursively all tests from test directory 
    $ find -name tests -type d | xargs -i limb_unit "{}" #running all tests from any directories called tests

But wait, **limb_unit** can do much more than that! We highly recommend read on all documentation below.

## File masks used by limb_unit
**limb_unit** by default uses the following file masks for tests files inclusion during recursive directory traversal:

* *test.php
* *Test.php
* *Test.class.php

Non matching file are simply ignored.

You can override this behavior using **-I/–include** option, e.g:

    $ limb_unit -I '*.php' tests

In this case tests from all php scripts will be included recursively from test directory.

Please note, these rules are only applied when directory is recursively traversed. When you execute tests for a single file these rules are skipped.

## Basic usage examples
Say we have the following files located somewhere in the file system:

    foo/a_test.php
    foo/b_test.php
    foo/bar/c_test.php

Each of *_test.php files is a module which consist of several test cases as following:

    //UnitTestCase is included automatically by limb_unit from SimpleTest library
    class MyFooTest extends UnitTestCase
    {
    ...
    }
 
    class MyBarTest extends UnitTestCase
    {
    ...
    }

Running the following command in the shell:

    $ limb_unit foo

Will yield executing all tests recursively from top directory: a_test.php, b_test.php and c_test.php.

Now let's run the following command:

    $ limb_unit foo/bar

This will only execute all tests from c_test.php.

You also can execute tests for each separate file, for example:

    $ limb_unit foo/a_test.php
    $ limb_unit foo/b_test.php
    $ limb_unit foo/bar/c_test.php

## Hierarchical fixture usage examples
**limb_unit** allows to have hierarchical fixtures for each directory. A fixture is a special testing environment which is required for tests to operate properly(e.g. loading dump into tests database, setting up directories layout for testing, etc).

Now fancy we have the following files:

    foo/.setup.php
    foo/.teardown.php
    foo/a_test.php
    foo/b_test.php
    foo/bar/.setup.php
    foo/bar/.teardown.php
    foo/bar/c_test.php
    foo/bar/d_test.php

Let's run the following command in the shell:

    $ limb_unit foo

This will result in executing files in the following order(*_test.php are test cases files):

1. foo/.setup.php
2. foo/a_test.php
3. foo/b_test.php
4. foo/bar/.setup.php
5. foo/bar/c_test.php
6. foo/bar/d_test.php
7. foo/bar/.teardown.php
8. foo/.teardown.php

A pair of scripts **.setup.php / .teardown.php** define a fixture for some directory: **.setup.php** — as name states clearly, is responsible for setup of the fixture, **.teardown.php** — for fixture cleanup. .setup.php is executed before actual running of tests, .teardown.php, accordingly, after.

Fixtures are plain PHP scripts. However fixture scripts are executed in the context of an object which allows you to pass the state of the fixture from .setup.php to .teardown.php using **$this**. Fixtures are optional, e.g. you can have either .teardow.php or .setup.php, or both.

Fixture quick example:

**.setup.php**

    <?php
    require_once('limb/dbal/src/lmbDbDump.class.php');
    $this->dump = new lmbDbDump(dirname(__FILE__) . '/.fixture/init_tests.sql');
    $this->dump->load();
    ?>

**.teardown.php**

    <?php
    $this->dump->clean();
    ?>

As you probably have already noted, fixtures are hierarchically «inherited» from upper directories when they are executed. The same rule applies even if you run tests for single files:

    $ limb_unit foo/bar/c_test.php

This will lead to executing the following files:

1. foo/.setup.php
2. foo/bar/.setup.php
3. foo/bar/c_test.php
4. foo/bar/.teardown.php
5. foo/.teardown.php

## Conditional execution of tests
**limb_unit** allows to skip conditionally tests located in a directory and below. This is quite useful when tests can't be executed because of some unsatisfied environment requirements, e.g. some PHP module or library is missing, database connection is not established and so on.

In order to conditionally run tests in a directory create **.skipif.php** script in it. Use PHP **return false/true** statement in this script depending on whether you want to proceed(return false) or skip(return true) tests in this directory.

Here's an example of such a script:

**.skipif.php**

    <?php
    return lmbToolkit :: instance()->getDefaultDbConnection()->getType() != 'mysql';
    ?>

In the example above if the current database connection is not of 'mysql' type all tests in this directory and below will be skipped.

## Test code coverage
Starting from 0.7.0 version **limb_unit** has support for measuring tests code coverage. **limb_unit** internally uses phpCoverage library for this purpose which it its turns depends on [Xdebug](http://xdebug.org/) PHP extension. That's why you have to setup Xdebug before running code coverage analysis (phpCoverage is already bundled with TESTS_RUNNER)

Basic test code coverage usage is as follows:

    $ limb_unit --cover=src tests

This will result in code coverage analysis of all scripts under **src** by tests in **tests** directory. The overall coverage report is printed into stdout and looks something like:

    ##############################################
     Code Coverage Summary: Code Coverage Report
       Total Files: 21
       Total Lines: 2259
       Total Covered Lines of Code: 392
       Total Missed Lines of Code: 192
       Total Lines of Code: 584
       Code Coverage: 67.12%
    ##############################################

You can also generate detailed html report. Use **–cover-report** option passing it a directory which should contain the report files:

    $ limb_unit --cover=src --cover-report=report tests

In this case the coverage report will be generated into **report** directory. Use your favorite browser in order to view the report files.

## Test entities filtering
**limb_unit** allows you to specify filters in command line for the following entities:

* test classes
* test class methods
* test class PHPDoc @group annotation tags

## Test classes filtering
Test classes filtering makes it possible to execute only those test classes which match the specified filter. The shell command below:

    $ limb_unit -T Foo,Bar test.php

..should run only **Foo** and **Bar** test classes found among others in test.php file(this true for any kind of arguments including directories)

## Test methods filtering
Test methods filtering allows you to run only those test methods which match the passed filter. For example, the following command:

    $ limb_unit -M testFoo,testBar test.php

..will execute only **testFoo** and **testBar** test methods omitting the rest ones found in all test cases of test.php file. This filter is especially useful in case you've spotted a bug in a large test case and would like to execute the problematic test method in isolation without resort to commenting the rest of the test case.

## Annotation filtering
Annotation **@group** tag filter allows you to execute only those test cases which have some specific @group annotation tags. For example, running the following command:

$ limb_unit -G db,auth test1.php test2.php

…will yield executing only those test cases from test1.php and test2.php files which have @group annotation tag matching **db,auth** filter, e.g:

    /**
     * @group db
     */
     class MyTest1 extends UnitTestCase
    ...
    /**
     * @group auth,db
     */
     class MyTest2 extends UnitTestCase
    ...

## Passing arbitrary configuration values
**limb_unit** allows you to pass any arbitrary configuration values as PHP defines using the following format:

    $ limb_unit KEY1=value1 KEY2=value2

In this case **value1** and **value2** will be defined respectively as **KEY1** and **KEY2** constants via PHP define calls. Please note, it's important for keys being written in *upper case* to be recognized as constants.

A more real life example should clarify the possible usage of this feature:

    $ limb_unit LOAD_DB=true my/tests

…and somewhere in my/tests/.setup.php:

    if(defined('LOAD_DB'))
      load_tests_db_dump();

## Customizing limb_unit
Usually before running tests for some application we need to setup a bunch of settings, e.g. PHP include_path, global constants, default database connection settings and so on. **limb_unit** provides basic means for including setup configuration PHP scripts(these are plain PHP scripts actually).

To include your custom PHP settings script use -c option or its long alias **–config=**, for example:

    $ limb_unit -c my_settings.php ...

**limb_unit** is also aware of **LIMB_TESTS_RUNNER_CONFIG** environment variable which can be used for passing configuration script path as well. However this variable is overridden with **–config** option.

In the example above my_settings.php script, will be included before running any tests. For example we can have the following configuration settings in such a script:

    <?php
    //setting up include_path
    set_include_path('/home/bob/dev/libA/' . PATH_SEPARATOR . get_include_path());
    //using CVS version of SIMPLE_TEST instead of shipped with TESTS_RUNNER
    @define('SIMPLE_TEST', '/home/bob/dev/external/simpletest-cvs/'); 
    //declaring path to some temporary directory
    @define('TMP_DIR', '/home/bob/tmp/'); 
    ?>

It's quite boring to type long command «limb_unit -c my_settings.php …» every time, that's why it makes sense to make a short command alias or use batch script in Windows

For example:

**.bashrc alias(*nix)**

    alias unit="limb_unit -c /home/bob/dev/tests/setup.php"

**unit.bat(Windows)**

    @echo off
    limb_unit -c /home/bob/dev/tests/setup.php %*

Don't forget putting this command somewhere available through Path environment variable

Now you can run tests in console using the following command:

    $ unit foo_test.php

But we can go even further in our **limb_unit** customization quest. Sometimes we need a better control over which version of PHP is necessary to be used, which php.ini settings it should accept and so on. All of this can be easily achieved if we execute **bin/limb_unit.php** directly from TESTS_RUNNER package instead of using **limb_unit** utility. For example:

**unit.bat**

    @echo off
    php d:/var/dev/limb3/trunk/limb/tests_runner/bin/limb_unit.php -c d:/var/dev/tests/setup.php %*

Here we use SVN version of TESTS_RUNNER package without **limb_unit** front end.

Or say we can have the following script which allows to use any version of PHP:

**unitv.bat**

    @echo off
    %1 d:/var/dev/limb3/trunk/limb/tests_runner/bin/limb_unit.php -c d:/var/dev/tests/setup.php %2 %3 %4 %5 %6

It can be used as follows:

    $ unitv php552 my_test.php
    $ unitv php551 my_test.php
    $ unitv php-debug my_test.php

## Customizing limb_unit for Limb3 packages testing
Here goes the full example of **limb_unit** customization as Limb developers usually have it for testing Limb3 packages.

* Create separate tests directory for settings, temporary directories, etc, e.g. /home/bob/dev/tests
* Create setup.php in this directory:

        <?php
        //firstly, adding current directory /home/bob/dev/tests into include_path
        //secondly, adding Limb3 directory into include_path
        set_include_path(dirname(__FILE__) . '/' . PATH_SEPARATOR . 
                         '/home/bob/dev/limb/3.x/' . PATH_SEPARATOR);
 
        //temporary directory will be /home/bob/dev/tests/var
        @define('LIMB_VAR_DIR', dirname(__FILE__) . '/var/');
        //setting up database connection settings
        @define('LIMB_DB_DSN', 'mysql://root:secret@localhost/all_tests?charset=utf8');
        ?>

* Since we put /home/bob/dev/tests as a first item in the include_path, Limb3 configuration settings will be searched in this directory first. It means that configuration settings will be searched in this directory first. Let's start with creating default database connection settings in **db.conf.php** in /home/bob/dev/tests/settings directory:

        <?php
        $conf = array('dsn' => LIMB_DB_DSN);//using constant from setup.php
        ?>

* It also make sense to override WACT settings so that it doesn't cache compiled templates and tags locations. Let's create **wact.conf.php** configuration file in /home/bob/dev/tests/settings as well:

        <?php
        $conf = array('forcescan' => true, 'forcecompile' => true);
        ?>

* Now let's create convenience alias **unit** in .bashrc for running **limb_unit**:

        alias unit="limb_unit -c /home/bob/dev/tests/setup.php"

* In case you are using Windows here's an example of unit.bat batch script:

        @echo off
        limb_unit -c c:/var/dev/tests/setup.php %*

*Checkout [custom papers](http://www.a-writer.com/) writing service
