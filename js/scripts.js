let functionAjax = function (auto) {

        //default settings.
        let pos = 1;
        let guery = '';
        let list_index = 0;
        let begin_date = '';
        let end_date = '';
        let cats = '';


        // get url without get params.
        let url = window.location.href.split('?')[0];

        // parse get params.
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);

        let get_pos = urlParams.get('position');
        let get_search_guery = urlParams.get('search_query');
        let get_begin_date = urlParams.get('begin_date');
        let get_end_date = urlParams.get('end_date');
        let get_cats = urlParams.get('search_cats');

        if (get_pos != null) {
            pos = get_pos;
        }

        if (get_search_guery != null) {
            guery = get_search_guery;
        }

        if (get_begin_date != null) {
            begin_date = get_begin_date;
        }

        if (get_end_date != null) {
            end_date = get_end_date;
        }

        if (get_cats != null) {
            cats = get_cats.split(',');//make array from get string!
        }

        // vars for ajax default values.
        let num = 1;
        let search_val;
        let search_begin_date;
        let search_end_date;
        let search_terms;

        // if user select something - then update query with user choise.
        // if there is no user's selection - fill query from get params.
        if (auto) {
            search_val = guery;
            search_begin_date = begin_date;
            search_end_date = end_date;
            num = pos;
            search_terms = cats;
        } else {
            search_val = jQuery('#search_query').val();
            search_begin_date = jQuery('#begin_date').val();
            search_end_date = jQuery('#end_date').val();
            search_terms = jQuery('#review_cat').val();
        }

        jQuery.ajax(
            {
                type: 'POST',
                url: window.obj.ajax_url,// url for WP ajax url (get on frontend! set in wp_localize_script like object).
                data: {
                    action: obj.plugin_acronym,// must be equal to add_action( 'wp_ajax_filter_plugin', 'ajax_filter_posts_query' ).
                    begin_date: search_begin_date,
                    end_date: search_end_date,
                    search_cats: search_terms,//search_terms,// jQuery('#review_cat').val(),
                    security: jQuery('#_wpnonce').val(),
                    page_number: num,
                    url: url,
                    search_query: search_val,
                },
                success: function (response) {
                   //
                    jQuery('#search_result').replaceWith(response.data.html);// change page content without refresh.

                    jQuery("#search_query").val(response.data.search_query);
                    jQuery('#begin_date').val(response.data.begin_date);
                    jQuery('#end_date').val(response.data.end_date);
                    //set review_cat selected options
                    let search_cats = response.data.search_cats;
                    jQuery.each(search_cats.split(","), function (i, cat) {
                        jQuery("#review_cat option[value='" + cat + "']").prop("selected", true);
                    });
                }
            }
        );
    }
;

jQuery(
    function () {// for first time page open.
        functionAjax(true);// make AJAX content update without check manual settings.
    }
);



jQuery(
    function ($) {
        $('#find_button').on(
            'click',
            function () {
                let length = $('#search_query').val().length;
                if (length < 3 && length > 0) {
                    alert('length of search query must start from 3 chars!');
                } else {
                    //  event.preventDefault();// cancel last blank for input.
                    functionAjax(false);// make AJAX content update with check manual settings.
                }
            }
        );
    }
);



jQuery(function($){ // use jQuery code inside this to avoid "$ is not defined" error
    $('#load_more').click(function(){
alert('666');
        var button = $(this);
       // if( data ) {
            button.text( 'More posts---' ).prev().before('<div><p>QASSDSCFEECCDDCSDWESXZ</p></div>'); // insert new posts
         //   misha_loadmore_params.current_page++;

           // if ( misha_loadmore_params.current_page == misha_loadmore_params.max_page )
             //   button.remove(); // if last page, remove the button

            // you can also fire the "post-load" event here if you use a plugin that requires it
            // $( document.body ).trigger( 'post-load' );
       // } else {
         //   button.remove(); // if no data, remove the button as well
       // }



    });
});



