Another one selfmade php framework.
# ki-frame

Why?

* Wanted straight-logic simple yet flexible framework
* Wanted embedded User LogPass, Social and Account functionality
* Didn't wanted to learn frameworks

Hence ki-frame is a framework playground, which for the moment comes into relatively stable one.

### Minimal useage

    include('path/core/init.php');
    KF::debug(1,1); //switch on for echo/errors just in case


    function templateOk(){
      return 'Hi!';
    }

    KF::bind('/', KF::code( templateOk ));
    KF::bind(404, KF::code('Wrong way'));

    KF::end();

### Structure

Mostly all useable functions are accessed by KF::* singletone which is defined in <KiFrame.php>

#### To be continued someday...
