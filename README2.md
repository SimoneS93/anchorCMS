#Anchor CMS addons

Access Anchor's data throught an simple, consistent, object-oriented interface.

###API
The script exposes 4 methods you can use to access your data:

 - **A()**: returns a collection of all your posts (*A* stands for *Article*)
 - **C()**: returns a collection of all your categories
 - **P()**: returns a collection of all your pages
 - **U()**: returns a collection of all your users

All of them allow the following actions:

 - **filter($key, $value)**: returns all objects in collection that match the pair
 - **first($key, $value)**: returns the first object in the collection that match the pair
 - **id($id)**: return the object with the given id
 - **slug($slug)**: return the object with the given slug
 - **sort($key)**: sorts the collection based on the key
 - **target()**: returns the 'current' object, based on the page you're on

*P()* has the following, too:

 - **isHomepage()**: returns true if current page is homepage
 - **isPostspage()**: returns true if current page is posts page

**NOTE 1**: you get some interesting attributes in your objects you should check via a *var_dump()*

**NOTE 2**: *filter()*, *first()* and *sort()* allow you to pass a custom callback to do filtering/sorting.

**NOTE 3**: the collections you get from *A()*, *C()*, *P()* and *U()* implements the *Countable* and *Iterator* interface: this lets you use them as arrays (see examples).


###Usage
Here are some common-use snippets:

*Sort posts by title and print them*

    <ul>
        <? foreach (A()->sort('title') as $post): ?>
            <li><?= $post->title; ?><li>
        <? endforeach; ?>
    </ul>

*Check if you're on the homepage. If yes, print it's content*

    <? if (P()->isHomepage()) print P()->target()->content; ?>

*Print a post custom field (call it 'justAfield')*

    <? print A()->id(10)->justAfield; ?> !no need for /article_custom_field()/

*Check how many pages are in your website*

    <? count(P()) or P()->count() ?>
