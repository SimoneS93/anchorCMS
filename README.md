AnchorCMS Addons
=========

Access all your AnchorCMS data throught a consistent and intuitive API, with an object-oriented approach!

###Prerequisites
 1. Copy **ooanchor.latest.php** in your theme's folder
 2. In your *header.php*, write:

        <?php theme_include('ooanchor.latest.php'); ?>

 3. In your *anchor/models/category.php* file, append the following:
  
        #after the *paginate* function
        public static function listing() {
            return static::where('id', '>', -1)->get();
        }

4. In your *anchor/models/page.php* file, append the following:

        #after the *active* function
        public static function listing() {
            return static::where('id', '>', 0)->get();
        }
        
        public static function id($id) {
            return static::where('id', '=', $id)->fetch();
        }

Now you're ready to see how simple it is to use.

###Usage
#####Fetch all records
To fetch all the records you just need a line:

    <?php
        #fetch all the categories
        $categories = ooanchor\categories::get();
        #fetch all the posts
        $posts = ooanchor\posts::get();
        #fetch all the pages
        $pages = ooanchor\pages::get();
    ?>

#####Fetch records by a key-value match
Want to fetch only the records that match a key-value pair? Easy as:

    <?php $posts_that_match = ooanchor\posts::get('your-key-here', 'your-value-here'); ?>

**NOTE**: if you're searching by a numeric value, you have to cast it to string, e.g:

    <?php $posts_with_id_5 = ooanchor\posts::get('id', '5'); ?>

#####Fetch a single record by a key-value match
You just need a single record? 

    <?php $first_by_admin = ooanchor\posts::first('author_name', 'admin'); ?>

#####Fetch custom records
You can even apply your own filter rule:

    <?php
        #get all the pages whose content contains 'hello'
        $custom_filtered_pages = ooanchor\pages::filter(function($page) {
            return strpos($page->content, 'hello') !== FALSE;
        }
    ?>

#####About the MODEL
When you run your queries with *get()* or *filter()*, you get an array of **MODEL** s, which is just a wrapper for an associative array with getters and setters. It's useful 'cause it's an object, so you can implement your custom behavior into it, if you want. It provides a useful **getAttr()** method which lets you specify a default value to return in case the attribute you're lookin for isn't set:

    <?php
        #given you got $post from a query
        $author_bio_or_default = $post->getAttr('author_bio', 'no bio found');
           #if the author_bio is empty, the default value will be returned
    ?>

#####Extended models
The models you get from your queries holds some bits of data regular Anchor's objects don't have:
 - **post->content**: the parsed content of your post
 - **post->url**: the url
 - **post->category_slug**: the slug of category the post belongs to
 - **page->content**: same as post
 - **page->url**: same as post
 - **page->parent_slug**: the slug of page's parent (empty if is a top-level page)

To have a list of all the attributes available, run a *first()* query and do a *var_dump()*
