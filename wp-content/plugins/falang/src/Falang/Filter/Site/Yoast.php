<?php

namespace Falang\Filter\Site;

class Yoast {

    /**
     * Constructor
     *
     * @from 1.3.8
     *
     * @update 1.3.40 add filter wpseo_breadcrumb_links
     * @update 1.3.61 fix _yoast_wpseo_canonical
     */
    public function __construct() {

        //add_filter('wpseo_robots', array($this, 'wpseo_translation_noindex'), 10, 2);
        //add_filter( 'yoast_seo_development_mode', '__return_true' );

        //for only translated language test default in the function
        add_filter('wpseo_title', array($this, 'wpseo_title'), 10, 2);
        add_filter('wpseo_opengraph_title', array($this, 'wpseo_og_title'), 10, 2);
        add_filter('wpseo_canonical', array($this, 'wpseo_canonical'), 10, 2);

        add_filter('wpseo_metadesc', array($this, 'wpseo_metadesc'), 10, 2);
        add_filter('wpseo_opengraph_desc', array($this, 'wpseo_opengraph_desc'), 10, 2);
        //add_filter('wpseo_opengraph_url',array($this, 'wpseo_opengraph_url'), 10, 2);

        add_filter( 'wpseo_should_save_indexable', array( $this, 'wpseo_should_save_indexable' ) );

        add_filter( 'wpseo_schema_organization',[ $this,'wpseo_schema_url'],10);
        add_filter( 'wpseo_schema_website',[ $this,'wpseo_schema_url'],10);
        add_filter( 'wpseo_schema_webpage',[ $this,'wpseo_schema_url'],10);

        //for all languages
        add_filter( 'wpseo_frontend_presentation', array( $this, 'translatePermalinks' ) );//this should be enabled for main_language. Maybe can be disabled for main language if we are sure that link on main language are always right(after disabling indexable generation)

        //translate breadcrumb
        add_filter( 'wpseo_breadcrumb_links', array( $this,'wpseo_breadcrumb_links' ));

    }

    public function wpseo_translation_noindex($robots,$presentation){
        //this is only to prevent robots to index translations. It's only needed for test period, And should be not used when everything is ok.
        if (!Falang()->is_default() ){$robots="noindex, nofollow"; }
        return $robots;
    }

    /*
     * @since 1.3.15  $data['@type'] is no more an array
     * @since 1.3.20 check $data['publisher']
     * @since 1.3.27 add isset on $data['publisher']['@id'] and  $data['potentialAction'][0]['target']
     * @since 1.3.30 php 8 waring fix $data is an array
     * @update 1.3.65 fix double slug in url
     */
    public function wpseo_schema_url($data){
        if (Falang()->is_default() ) return $data;
        //there is problem:if indexable is empty -then it adds double /language-codes ex /uk/uk/

        $home_url=home_url('/');
        $home_default =str_replace('/'.Falang()->current_language->slug,'',$home_url);
        $curr_slug = Falang()->current_language->slug;
        if (isset($data['url'])){
            $data['url']=str_replace($home_default, $home_url,$data['url']);
            //remove duplicate slug if exist
            $data['url'] = str_replace('/'.$curr_slug.'/'.$curr_slug.'/','/'.$curr_slug.'/',$data['url']);
        }
        if (isset($data['@id'])){
            $data['@id']=str_replace($home_default, $home_url,$data['@id']);
            $data['@id'] = str_replace('/'.$curr_slug.'/'.$curr_slug.'/','/'.$curr_slug.'/',$data['@id']);
        }
        //additional replacments because some of them were in internal array. Not all links should be changed thats why it's targeting only selected.
        if ($data['@type']=='Organization'){

            $data['logo']['@id']=str_replace($home_default, $home_url,$data['logo']['@id']);
            $data['image']['@id']=str_replace($home_default, $home_url,$data['image']['@id']);
        }
        elseif ($data['@type']=='WebSite'){

            if (isset($data['publisher']) && isset($data['publisher']['@id'])){
                $data['publisher']['@id']=str_replace($home_default, $home_url,$data['publisher']['@id']);
            }
            if (isset( $data['potentialAction']) && isset($data['potentialAction'][0]) && isset( $data['potentialAction'][0]['target'])){
                $data['potentialAction'][0]['target']=str_replace($home_default, $home_url,$data['potentialAction'][0]['target']);
            }
        }

        elseif($data['@type']=='ItemPage' ){
            $data['isPartOf']=str_replace($home_default, $home_url,$data['isPartOf']);
        }

        return $data;
    }


    public function wpseo_title($title,$presentation)
    {
        return $this->translate_title($title,$presentation,array('_yoast_wpseo_title','post_title'));
    }

    public function wpseo_og_title($title,$presentation)
    {
        return  $this->translate_title($title,$presentation,array('_yoast_wpseo_opengraph-title','_yoast_wpseo_title','post_title'));
    }

    public function wpseo_canonical($description,$presentation)
    {
        return  $this->translate_description($description,$presentation,array('_yoast_wpseo_canonical','_yoast_wpseo_canonical'));
    }

    public function wpseo_metadesc($description, $presentation)
    {
        return $this->translate_description($description, $presentation,array('_yoast_wpseo_metadesc','post_excerpt'));
    }

    public function wpseo_opengraph_desc($description, $presentation)
    {
        return $this->translate_description($description, $presentation,array('_yoast_wpseo_opengraph-description','_yoast_wpseo_metadesc','post_excerpt'));
    }

    /*
     * @update 1.3.57 fix from Stamatios Aronis
     * @update 1.3.64 fix yoast title translation (no variable in the title)
     * @update 1.3.65 fix yoast title translation (original value was use with separator)
     * */
    private function translate_title($title,$presentation,array $optionNames) {
        if(Falang()->is_default()) return $title;
        $object_type = $presentation->model->object_type;
        if ($object_type == 'term'){
            //nothing to do the title is already translated.
            //format tested %%term_title%% Archivy %%page%% %%sep%% %%sitename%%
        } else {
            //post, page, product..
            $language = Falang()->get_current_language();
            $post_id=$presentation->model->object_id;
            $post = get_post ($post_id);
            //normal post title translation
            $falang_post = new \Falang\Core\Post($presentation->model->object_id);

            if ($post && $falang_post->is_post_type_translatable($post->post_type)) {

                $last_key = $this->array_key_last($optionNames);
                foreach ($optionNames as $key => $optionName) {
                    //last key post_title
                    if ($key == $last_key) {
                        $title = $falang_post->translate_post_field($post, 'post_title', $language, $title);
                    } else {
                        //specific yoast title set
                        $yoast_wpseo_title = $falang_post->translate_post_meta($post, $optionName, true, $language, '');
                        if (!empty($yoast_wpseo_title)) {
                            $title = $yoast_wpseo_title;
                            break;
                        }
                    }
                }
                //1.3.64 change due to improvment
                //nothing to filter use directly the title
                //1.3.65 the title to use is the translated
                if (strpos($title, '%%') !== false) {
                    $title = wpseo_replace_vars($title, array("post_title" => $title));
                }

            }
        }

        return $title;

    }

    /*
     * array_key_last only since php 7
     * */
    private function array_key_last(array $array) {
        if( !empty($array) ) return key(array_slice($array, -1, 1, true));
    }

    /**
     * Filters the meta description for stories.
     *
     * @param string                 $description The description sentence.
     * @param Indexable_Presentation $presentation The presentation of an indexable.
     * @return string The description sentence.
     *
     * @update 1.3.65 fix category description (need change in wpml-config.xml from yoast)
     */
    public function translate_description($description, $presentation,$optionNames) {
        if(Falang()->is_default()) return $description;
        $object_type = $presentation->model->object_type;
        $object_id=$presentation->model->object_id;
        $language = Falang()->get_current_language();

        if ($object_type == 'term'){
            //load the descripion set in options (or register by the string )
            $description = falang__($description);
        } else {
            //post, page, product
            $object_id=$presentation->model->object_id;
            $post = get_post ($object_id);
            $falang_post = new \Falang\Core\Post($object_id);

            if ($post && $falang_post->is_post_type_translatable($post->post_type)){

                $last_key = $this->array_key_last($optionNames);
                foreach ($optionNames as $key => $optionName) {
                    //last key post_excerpt
                    if ($key == $last_key) {
                        $description = $falang_post->translate_post_field($post, 'post_excerpt', $language,'' );
                        if ( empty($description)){
                            $description = wp_trim_words($falang_post->translate_post_field($post, 'post_content', $language, $description));
                        }
                    } else {
                        //specific yoast title set
                        $yoast_description = $falang_post->translate_post_meta($post, $optionName, true ,$language,'' );
                        if (!empty($yoast_description)) {
                            $description = $yoast_description;
                            break;
                        }
                    }
                }
            }
        }

        $description = wpseo_replace_vars( $description, $presentation );
        return $description;

    }

    /**
     * Filter: 'wpseo_opengraph_url' - Allow changing the Yoast SEO generated open graph URL.
     *
     * @api string $url The open graph URL.
     *
     * @param Indexable_Presentation $presentation The presentation of an indexable.
     */
    public function wpseo_opengraph_url($wpseo_opengraph_url, $presentation) {
        $language = Falang()->get_current_language();
        $url = Falang()->get_translated_url( $language );
        return $url;
    }


    /**
     * Translate permalinks.
     *
     * @param Indexable_Presention $presentation The indexable presentation.
     *
     * @return Indexable_Presention
     */
    public function translatePermalinks( $presentation ) {
        //it also returns correct link for main language, if somehow in indexables table is wrong link saved. It should be also executed on main language. (it is right now)
        $presentation = clone $presentation;

        if ( 'post' === $presentation->model->object_type ) {
            $presentation->model->permalink = get_permalink( $presentation->model->object_id );
        } elseif ( 'term' === $presentation->model->object_type ) {
            $presentation->model->permalink = get_term_link( $presentation->model->object_id );
        }

        return $presentation;
    }

    /*
     * preventing saving indexables on non default language
     * */
    public function wpseo_should_save_indexable(){
        if(!Falang()->is_default()) return false;
        return true;
    }

    /*
     * Translate Breadcrum title
     * @from 1.3.40
     *
     * @param array $links The indexable presentation.
     * @return array $links
     * */
    public function wpseo_breadcrumb_links($links){
        if(Falang()->is_default()) return $links;
        foreach ($links as $idx => $link){
            if (isset($link['text']) && isset($link['id'])){
                $language = Falang()->get_current_language();
                $post = get_post ($link['id']);
                //normal post title translation
                $falang_post = new \Falang\Core\Post($link['id']);

                if ($post && !Falang()->is_default() && $falang_post->is_post_type_translatable($post->post_type)){
                    $links[$idx]['text'] = $falang_post->translate_post_field($post, 'post_title', $language, $link['text']);;
                }
            }
        }
        return $links;
    }
}