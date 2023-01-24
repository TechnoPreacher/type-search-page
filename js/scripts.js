let functionAjax = function (auto) {
//   alert(jQuery('#review_cat').val());


    //default settings.
    let pos = 1;
    let search_guery = '';
    let list_index = 0;

    // get url without get params.
    let url = window.location.href.split('?')[0];

    // parse get params.
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);

    let get_pos = urlParams.get('position');
    let get_search_guery = urlParams.get('search_query');
    let get_list_index = urlParams.get('search_list');

    if (get_pos != null) {
        pos = get_pos;
    }

    if (get_search_guery != null) {
        search_guery = get_search_guery;
    }

    if (get_list_index != null) {
        list_index = get_list_index;
    }

    // vars for ajax default values.
    let num = 1;
    let search_val;
    let search_index;

    // if user select something - then update query with user choise.
    // if there is no user's selection - fill query from get params.
    if (auto) {
        search_index = list_index;
        search_val = search_guery;
        num = pos;
    } else {
        search_val = jQuery('#search_query').val();
        search_index = jQuery('#search_list').prop('selectedIndex');
    }

    jQuery.ajax(
        {
            type: 'POST',
            url: window.obj.ajax_url,// url for WP ajax url (get on frontend! set in wp_localize_script like object).
            data: {
                action: obj.plugin_acronym,// must be equal to add_action( 'wp_ajax_filter_plugin', 'ajax_filter_posts_query' ).
                search_list: search_index,
                search_cats:jQuery('#review_cat').val(),
                security: jQuery('#_wpnonce').val(),
                page_number: num,
                url: url,
                search_query: search_val,
            },
            success: function (response) {
                jQuery('#search_result').replaceWith(response.data.html);// change page content without refresh.
                jQuery("#search_query").val(response.data.search_query);
                jQuery('#search_list').prop('selectedIndex', response.data.search_list);
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
        // set many selectors and events belong to one handler.
        $('#search_query,#find_button,#search_list').on(
            'keypress change click select',
            function (event) {
                // такая конструкция нужна чтоб в текстовых инпутах слать аякс только по энтеру или кнопке.
                if ((this.id === 'search_query') || (this.id === 'find_button') || (this.id === 'search_list')) {
                    if ((event.which === 13) || (this.id === 'find_button') || (this.id === 'search_list')) {
                        //minimum length warning!
                        let length = $('#search_query').val().length;
                        if (length < 3 && length > 0) {
                            alert('length of search query must start from 3 chars!');
                        } else {
                            //  event.preventDefault();// cancel last blank for input.
                            functionAjax(false);// make AJAX content update with check manual settings.
                        }
                    }
                }
            }
        );
    }
);




