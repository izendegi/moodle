Gap-select question type

This question type allows students to complete a paragraph of text by selecting the
missing words using drop-down menus. You can make questions like this using the
Cloze question type, but this question type is easier for teachers to get up, and
presents the feedback in a more accessible way.

The question type was created by Jamie Pratt (http://jamiep.org/) paid for by
the Open University (http://www.open.ac.uk/).

This version of this question type is compatible with Moodle 3.10+. There are
other versions available for Moodle 2.3+ (see https://github.com/moodleou/moodle-qtype_gapselect).

Note that this is a customisation of the built-in Gap-select ('Select missing words') question type,
with support for importing and exporting from and to Word tables using the WordTable plugin.

To install using git, type this command in the root of your Moodle install
    rm -rf question/type/gapselect (Linux)
    deltree/y question/type/gapselect (Windows)
    git clone -b MOODLE_311_STABLE git@github.com:ecampbell/moodle-qtype_gapselect.git question/type/gapselect
Then add question/type/gapselect to your git ignore.

Alternatively, download the zip from
    https://github.com/ecampbell/moodle-qtype_gapselect/zipball/MOODLE_310_STABLE
unzip it into the question/type folder, and then rename the new folder to gapselect.
