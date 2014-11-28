# limb_unit — инструмент для выполнения SimpleTest тестов
Документация актуальна для версии TESTS_RUNNER-1.0.0-stable

Пакет TESTS_RUNNER, в состав которого входит limb_unit, **не имеет зависимостей** от других пакетов Limb3

## Описание
**limb_unit** — удобный инструмент для PHP5 способный выполнять [SimpleTest](http://www.simpletest.org/) тесты непосредственно из файловой системы.

Основные возможности:

* Возможность запуска тестов как по одиночке, так и рекурсивно из директории
* Поддержка иерархических фикстур для директорий
* Условное выполнение тестов
* Средства анализа покрытия кода тестами

## Установка
Вот и все! Попробуйте выполнить команду:

    $ limb_unit -h

Если все установилось правильно, вы должны увидеть краткую справку по использованию **limb_unit**.

    Usage:
      limb_unit [OPTIONS] <file|dir> [<file1|dir1>, ... <fileN|dirN>]
      Advanced SimpleTest unit tests runner. Finds and executes unit tests within filesystem.
    Arguments:
      <file|dir> [<file1|dir1>, ... <fileN|dirN>] - a list of files/directories, globs are supported(e.g. '*')
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

## Примеры использования limb_unit для нетерпеливых

    $ limb_unit my_test.php      #подключение модуля с тестами и выполнение всех тестов модуля
    $ limb_unit MyTest.class.php #выполнение тестового класса MyTest
    $ limb_unit *_test.php       #подключение всех модулей, подходящих под указанную *_test.php маску
    $ limb_unit tests            #выполнение всех тестов из директории tests
    $ find -name tests -type d | xargs -i limb_unit "{}" #выполнение тестов из всех директорий tests

Однако, **limb_unit** способен на большее! Поэтому настоятельно рекомендуется изучить документацию подробнее (см. ниже).

## Файловые маски для тестовых файлов
**limb_unit** по-умолчанию использует следующие файловые маски для подключения файлов с тестами во время обхода директорий:

* *test.php
* *Test.php
* *Test.class.php

Файлы, не попавшие ни под одну из масок, просто игнорируются.

Это поведение можно изменить, используя **-I/–include** опцию, например:

    $ limb_unit -I '*.php' tests

В этом случае будут рекурсивно подключены *все* php скрипты с тестами из директории tests.

Стоит отметить, что данные правила применяются только при рекурсивном обходе директорий. При выполнении отдельного файла эти правила игнорируются.

## Базовые примеры использования

Например, у нас есть следующая простая схема расположения тестов в файловой системе:

    foo/a_test.php
    foo/b_test.php
    foo/bar/c_test.php

Каждый из файлов *_test.php представляет из себя модуль с одним или несколькими тестовыми прецедентами, например:

    //UnitTestCase подключается утилитой limb_unit из библиотеки SimpleTest автоматически
    class MyFooTest extends UnitTestCase
    {
    ...
    }
 
    class MyBarTest extends UnitTestCase
    {
    ...
    }

Выполнение следующей команды из консоли:

    $ limb_unit foo

Приведет к тому, что будут выполнены тесты из файлов a_test.php, b_test.php, c_test.php.

Выполним теперь такую команду:

    $ limb_unit foo/bar

В результате будут выполнены тесты из файла c_test.php.

Можно выполнять и тесты по отдельности для каждого файла, например:

    $ limb_unit foo/a_test.php
    $ limb_unit foo/b_test.php
    $ limb_unit foo/bar/c_test.php

## Примеры использования фикстур
**limb_unit** позволяет назначать иерархические фикстуры для каждой отдельной директории. В данном случае фикстура - набор некоторых инициализационных настроек.

Представим, что теперь у нас есть следующие файлы:

    foo/.setup.php
    foo/.teardown.php
    foo/a_test.php
    foo/b_test.php
    foo/bar/.setup.php
    foo/bar/.teardown.php
    foo/bar/c_test.php
    foo/bar/d_test.php

Выполним следующую команду в консоли:

    $ limb_unit foo
    
Это приведет к тому, что файлы будут подключены в таком порядке(из файлов *_test.php будут также выполнены тесты):

1. foo/.setup.php
2. foo/a_test.php
3. foo/b_test.php
4. foo/bar/.setup.php
5. foo/bar/c_test.php
6. foo/bar/d_test.php
7. foo/bar/.teardown.php
8. foo/.teardown.php

Пара файлов **.setup.php / .teardown.php** определяют фикстуру для конкретной директории: **.setup.php** — установка фикстуры, **.teardown.php** — ее удаление. .setup.php отрабатывает до выполнения тестов, .teardown.php, соответственно, после. Файлы фикстуры обычные PHP скрипты. Однако есть небольшое замечание: эти файлы подключаются внутри объекта, поэтому можно передавать состояние фикстуры из .setup.php в .teardown.php, используя $this контекст. Файлы фикстур являются опциональными(например, можно иметь только .setup.php или .teardown.php). Пример фикстуры:

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

Как вы уже, наверное, успели заметить фикстуры «наследуются» из вышестоящих директорий, т.е выполняются каскадно. Это же правило справедливо и при выполнении тестов для отдельных файлов.

    $ limb_unit foo/bar/c_test.php

Это приведет к следующему подключению файлов:

1. foo/.setup.php
2. foo/bar/.setup.php
3. foo/bar/c_test.php
4. foo/bar/.teardown.php
5. foo/.teardown.php

## Условное выполнение и пропуск тестов
Утилита **limb_unit** позволяет пропускать тесты в директории по некоторому условию. Это бывает полезно, когда необходимо пропустить тесты, которые требуют особых условий среды, например, наличие PHP модуля, библиотеки, соединения с БД и проч.

Для контроля выполнения тестов в директории следует создать скрипт **.skipif.php** в этой директории. Используя PHP конструкцию **return**, скрипт обязан вернуть true или false, соответственно, если необходимо пропустить или продолжить выполнение тестов в директории.

Пример подобного скрипта:

**.skipif.php**

    <?php
    return lmbToolkit :: instance()->getDefaultDbConnection()->getType() != 'mysql';
    ?>

Данный скрипт пропустит выполнение тестов в директории, если текущее подключение к БД не типа 'mysql'.

В прежних версиях пакета TESTS_RUNNER скрипт .skipif.php назывался .ignore.php. Скрипты .ignore.php поддерживаются и сейчас, однако в будущем мы намереваемся прекратить эту поддержку.

## Анализ покрытия кода тестами
Начиная с версии 0.7.0, в **limb_unit** появилась поддержка анализа покрытия кода тестами. **limb_unit** использует для этих целей библиотеку phpCoverage. phpCoverage опирается на данные, получаемые при помощи расширения [Xdebug](http://xdebug.org/), поэтому перед анализом покрытия кода тестами его необходимо установить. Приведем пример базового использования:

    $ limb_unit --cover=src tests

Эта команда приведет к тому, что во время выполнения тестов в директории **tests** на предмет покрытия будут анализироваться исходные файлы в директории **src**. После выполнения будет выведен общий отчет по покрытию кода тестами, например такой:

    ##############################################
     Code Coverage Summary: Code Coverage Report
       Total Files: 21
       Total Lines: 2259
       Total Covered Lines of Code: 392
       Total Missed Lines of Code: 192
       Total Lines of Code: 584
       Code Coverage: 67.12%
    ##############################################

Также возможно сгенерировать подробный html отчет по покрытию тестами. для этого необходимо использовать ключ **–cover-report**, передав в качествего его значения некоторую директорию.

    $ limb_unit --cover=src --cover-report=report tests

Отчет будет сгенерирован в директорию **report**. Отчет представляет из себя набор подробных html файлов, для просмотра которых необходимо использовать браузер.

## Фильтрация тестовых сущностей
**limb_unit** позволяет указывать различные фильтры в командной строке для следующих тестовых сущностей:

* тестовые классы
* тестовые методы
* аннотации PHPDoc @group тестовых классов

### Фильтрация тестовых классов
Фильтрация тестовых классов позволяет выполнять только те тесты, которые подходят под указанный фильтр. Следующая команда:

    $ limb_unit -T Foo,Bar test.php

..выполнит только тестовые классы **Foo** и **Bar**, найденные среди прочих в файле test.php(фильтр будет отрабатывать для любого количества аргументов и даже при рекурсивном обходе директорий)

### Фильтрация тестовых методов
Фильтрация тестовых методов позволяет выполнять только те методы тестов, которые подходят под указанный фильтр. На пример, следующая команда:

    $ limb_unit -M testFoo,testBar test.php

..выполнит только методы **testFoo** и **testBar** среди всех тестовых классов, найденных в файле test.php. Этот фильтр особенно полезен, когда была обнаружена ошибка в одном из тестовых методов и необходимо выполнять только этот тестовый метод на время устранения неполадок. Без подобного фильтра обычной практикой является комментирование всех тестовых методов кроме необходимого, что довольно неудобно.

### Фильтрация по аннотациям
Аннотационный фильтр позволяет выполнять только те тествые классы, у которых аннотация @group подходит под указанный фильтр. К примеру, команда:

    $ limb_unit -G db,auth test1.php test2.php

…приведет к выполнению только тех тестовых классов из файлов test1.php и test2.php, у которых аннотация @group подходит под фильтр **db,auth**. Пример аннотации:

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

## Передача конфигурационных параметров
**limb_unit** позволяет передавать любые конфигурационные параметры через командную строку при помощи следующего формата:

    $ limb_unit KEY1=value1 KEY2=value2

В этом случае значения **value1** и **value2** будут определенны соответственно как **KEY1** и **KEY2** константы при помощи PHP define функций. Стоит заметить что, ключи параметров должны быть набраны в *верхнем регистре*, чтобы **limb_unit** смог определить, что это константы.

Реальный пример из жизни должен прояснить возможное предназначение подобных параметров:

    $ limb_unit LOAD_DB=true my/tests

…а где-то в my/tests/.setup.php:

    if(defined('LOAD_DB'))
      load_tests_db_dump();

## Пример кастомизации limb_unit
Зачастую бывает так, что для выполнения тестов в приложении требуются некоторые дополнительные настройки. Например, для выполнения тестов на пакеты Limb3 необходимо выставить значение константы LIMB_VAR_DIR(директория с временными файлами) или параметры доступа к тестовой БД и проч. **limb_unit** имеет базовые средства для подключения настроечных скриптов, которые, по сути, являются обычными PHP файлами.

Для подключения настроечных скриптов используется опция **-c** или ее длинный аналог **–config=**, например:

    $ limb_unit -c my_settings.php ...

Также **limb_unit** «знает» о переменной окружения **LIMB_TESTS_RUNNER_CONFIG**, с помощью которой можно задать путь до конфигурационных настроек. Использование переменной окружения является альтернативой использования опции **–config**.

Скрипт my_settings.php(а следовательно и настройки), будет подключен до подключения кода тестов. Пример подобных настроек может быть таким:

    <?php
    //используем cvs версию SIMPLE_TEST вместо встроенной в TESTS_RUNNER
    @define('SIMPLE_TEST', '/home/bob/dev/external/simpletest-cvs/'); 
    //объявляем путь до временной директории
    @define('APP_TMP_DIR', '/home/bob/dev/tmp/'); 
    ?>

Естественно, каждый раз набирать длинную команду «limb_unit -c my_settings.php …» занятие довольно утомительное, поэтому рекомендуется сделать короткий алиас на эту команду или средствами shell или, как, например, в Windows, создав .bat скрипт и поместив его в директорию, включенную в Path. Например:

**.bashrc(*nix)**

    alias unit="limb_unit -c /home/bob/dev/tests/setup.php"

**unit.bat(Windows)**

    @echo off
    limb_unit -c /home/bob/dev/tests/setup.php %*

Теперь в консоли тесты можно удобным образом запускать так:

    $ unit foo_test.php

Можно пойти чуть дальше и сделать еще более кастомизированную версию **limb_unit**. Дело в том, что порой необходимо протестировать код под разными версиями PHP, с дебаггером, разными модулями и проч. Каждый раз править php.ini занятие крайне утомительное, поэтому можно сделать следующее — запускать **limb_unit** в обход скрипта, поставляемого вместе с PEAR, напрямую подключая код пакета TESTS_RUNNER. Для этого всего лишь требуется отдать на исполнение PHP интерпретатору скрипт bin/limb_unit.php из пакета TESTS_RUNNER. Например:

**unit.bat**

    @echo off
    php d:/var/dev/limb3/trunk/limb/tests_runner/bin/limb_unit.php -c d:/var/dev/tests/setup.php %*

Здесь уже разработчик не ограничен использовать ту или иную версию PHP, можно сделать даже несколько версий подобных скриптов под разные версии PHP, с разными настройками и проч. Например:

**unitv.bat**

    @echo off
    %1 d:/var/dev/limb3/trunk/limb/tests_runner/bin/limb_unit.php -c d:/var/dev/tests/setup.php %2 %3 %4 %5 %6

Использование:

    $ unitv php552 my_test.php
    $ unitv php551 my_test.php
    $ unitv php-debug my_test.php

## Пример кастомизации limb_unit для выполнения Limb3 тестов
Для полной картины здесь приводится пример кастомизации **limb_unit** в том виде, как это практикуется у Limb3 разработчиков.

* Создаем отдельную директорию, которая будет из себя представлять тестовый проект для всех тестов, выполняемых при помощи **limb_unit**, например /home/bob/dev/tests
* Создаем в ней скрипт setup.php примерно такого содержания:

        <?php
        //во-первых добавляем директорию /home/bob/dev/tests в include_path
        //во-вторых добавляем директорию с Limb3 в include_path
        set_include_path(dirname(__FILE__) . '/' . PATH_SEPARATOR . 
                     '/home/bob/dev/limb/3.x/' . PATH_SEPARATOR);
 
        //директория с временными файлами будет расположена в /home/bob/dev/tests/var
        @define('LIMB_VAR_DIR', dirname(__FILE__) . '/var/');
        //устанавливаем параметры доступа к БД
        @define('LIMB_DB_DSN', 'mysql://root:secret@localhost/all_tests?charset=utf8');
        ?>

* Т.к мы добавили директорию /home/bob/dev/tests в include_path, то теперь поиск настроек будет происходить и в этой директории. Это означает, что мы можем хранить тестовые настройки в этой директории. Начнем с конфигурационного файла **db.conf.php**, в котором хранятся настройки для подключения к БД. Создадим этот файл в директории /home/bob/dev/tests/settings:

        <?php
        $conf = array('dsn' => LIMB_DB_DSN);//используем константу из setup.php
        ?>

* Теперь для удобства запуска тестов создадим алиас **unit** в .bashrc для **limb_unit**, например, назовем его unit:

        alias unit="limb_unit -c /home/bob/dev/tests/setup.php"

* Пример схожего **unit.bat** скрипта для Windows:

        @echo off
        limb_unit -c c:/var/dev/tests/setup.php %*
