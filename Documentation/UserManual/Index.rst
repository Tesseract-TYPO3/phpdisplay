.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _user-manual:

User manual
===========


.. _user-manual-setting-up-a-template:

Setting up a template
---------------------

#. Create a new record of type PHP based Display on a page or a sysfolder.
#. Define a value for field "Template path" by
   picking a file using the file browser.

   .. figure:: ../../Images/PhpDisplayRecord.png
   	  :alt: A typical PHP Display record

      A PHP Display element and all its input fields

   .. important::

      Using the :code:`EXT:` syntax is not recommended anymore. It will likely be dropped entirely
      in a future version, to make things more consistent with the TYPO3 CMS Core.

      If you store your templates inside an extension, you should create a File Storage pointing
      to that extension, so that you can pick files from there using the file browser.


#. Start editing the file. Variable :code:`$datastructure` contains the result of the data provider and
   can be used to manipulate data in the template. Some **useful examples** can be taken from the files
   in the :file:`Samples` folder.

.. code-block:: php

    <?php
        // a good starting point is to display / debug the structure
        print_r($datastructure);
    ?>


.. _user-manual-available-variables:

Available variables
-------------------

==============  =================================================  ======
variable name   Description                                        Type
--------------  -------------------------------------------------  ------
$datastructure  The Data Structure passed by the controller which  Array
                contains the records
--------------  -------------------------------------------------  ------
$filter         The values of the Data Filter                      Array
--------------  -------------------------------------------------  ------
$controller     The Display Controller itself which is the parent  Object
                object
==============  =================================================  ======


.. _user-manual-syntax-overview:

Syntax overview
---------------

As said, PHP Display is using PHP as a language template. To avoid confusion whenever writing PHP
code within HTML, it is recommended to use the syntax below:

.. code-block:: php

    // loop
    <?php foreach($datastructure as $record): ?>
        <?php print $record['title'] ?>
    <?php endforeach ?>

    // condition
    <?php if($foo == 'foo'): ?>
        Test has been validated as TRUE
    <?php else: ?>
        Test has been validated as TRUE
    <?php endif ?>


Basically, a very basic PHP based template can looks like:

.. code-block:: html

    Hello World!

    Here is a list of pages:
    <ul>
        <?php foreach($datastructure['page']['records'] as $record): ?>
            <li>
                <?php print $record['title'] ?>
            </li>
        <?php endforeach ?>
    </ul>

