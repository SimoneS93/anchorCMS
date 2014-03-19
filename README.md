AnchorCMS Addons
=========

The simple classes provided let you access your AnchorCMS data through a unified entrypoint, that is an instance of the *model* class. It's just a wrapper for an associative array, that exposes getters and setters for a more robust access to it's internals. 
You can use all of the classes or just those you need (*base* and *model* are mandatory): do you only need an object-oriented approach to work with your blog posts? Just include *posts.php* and so on.

####How to use
After including the classes you need, you can start using them. They all provide a consistent interface, so are quite similar in usage. An example:

    <?php
        //retrive all menu items
        $menuItems = data\\menu::get();
        
        //retrieve all the categories
        $categories = data\\categories::get();
        
        //retrieve the current category
        $ccategory = data\\categories::current();
          //print $ccategory->name;
        
        //retrieve all posts from the current category
        $posts = data\\posts::get('category', $ccategory->id);
        
        //retrieve the post with ID = 5
        $post5 = data\\posts::get('id', 5);
            //$post5 now holds it's custom fieds, too
            //print $post5->a_custom_field_name;
        
    ?>
    
At the moment, you can't do much more that this, still it can be very useful.

####About the *get()*
All of the *data\\...::get()* can be called with no arguments, in which case they return all the records, or you can pass them a key-value pair, to retrieve a single record.
        


