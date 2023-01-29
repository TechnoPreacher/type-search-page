let functionAjax = function (auto) {

    //default settings.
    let pos = 1;
    let guery = '';
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
        num = obj.current_page; //for paged when button 'load more' fired
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
            error: function (error_response) {
                obj.current_page = parseInt(obj.current_page) - 1;
            },
            success: function (response) {
                let data = response.data;
                if (data.pagination_type == 'more') {

                    if (obj.current_page == 1) {
                        jQuery('#search_result').replaceWith(data.html);
                        if (data.returned_posts_num > 0) {//show button if some posts returned
                            jQuery('#load_more').show();
                        } else {
                            jQuery('#load_more').hide();
                        }
                    } else {
                        jQuery('#search_result').append(data.html);
                    }
                }

                if (data.pagination_type == 'digit') {
                    jQuery('#search_result').replaceWith(data.html);// change page content without refresh.
                }

                jQuery("#search_query").val(data.search_query);
                jQuery('#begin_date').val(data.begin_date);
                jQuery('#end_date').val(data.end_date);

                //set review_cat selected options
                let search_cats = response.data.search_cats;
                if (search_cats != null) {
                    jQuery.each(search_cats.split(","), function (i, cat) {
                        jQuery("#review_cat option[value='" + cat + "']").prop("selected", true);
                    });
                }

                if (response.data.remove == true) {
                    jQuery('#load_more').hide(); // if no data, hide button
                }
            }
        }
    );
};

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
                    obj.current_page = 1;
                    functionAjax(false);// make AJAX content update with check manual settings.
                }
            }
        );
    }
);

jQuery(function ($) { // use jQuery code inside this to avoid "$ is not defined" error
    $('#load_more').click(function () {
        obj.current_page = parseInt(obj.current_page) + 1;
        functionAjax(false);
    });
});



