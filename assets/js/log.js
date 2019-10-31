$(document).ready(function () {

    const $timeline = $('.timeline');
    const $shablon = $('.timeline__shablon');
    const $stop_updating_hover_handler = $('.stop_updating_hover_handler');
    const $status_stop = $('.status_stop');
    const $status_updating = $('.status_updating');


    let updating = true;

    updateList();

    $stop_updating_hover_handler.on('mouseover', function () {
        updating = false;
        $status_stop.removeClass('d-none');
        $status_updating.addClass('d-none');
    });

    $stop_updating_hover_handler.on('mouseleave', function () {
        updating = true;
        $status_stop.addClass('d-none');
        $status_updating.removeClass('d-none');
    });


    function updateList() {

        setTimeout(function () {

                $.ajax({
                    url: 'log.log',

                    success: function (response) {
                        if (updating) {
                            const data = JSON.parse(response);
                            $timeline.html('');
                            for (let i = 0; i < data.length; i++) {
                                insertListItem(data[i], i);
                            }
                        }

                        updateList();
                    }
                });

            },
            500);
    }

    function insertListItem(item_info, i) {
        let $new_item = $shablon.clone();

        $new_item.removeClass('d-none').removeClass('timeline_shablon').addClass('timeline_item');

        $new_item.find('.shablon__title').text(item_info['title']);

        if (item_info['type'] == 'start')
        {
            $new_item.addClass('bg-light');
        }

        $new_item.find('.shablon__proccess').text(item_info['proccess']);
        const color = hashStringToColor(item_info['proccess']);
        $new_item.find('.shablon__proccess').css('background', color);

        switch (item_info['type']) {
            case 'error':
                $new_item.find('.shablon__title').addClass('text-danger');
                break;

            case 'secondary':
                $new_item.find('.shablon__title').addClass('text-secondary');
                break;
            case 'warning':
                $new_item.find('.shablon__title').addClass('text-warning');
                break;
        }


        $new_item.find('.shablon__date').text(item_info['date']);
        $new_item.find('.shablon__content').text(item_info['content']);

        $timeline.append($new_item);
    }


});


function djb2(str) {
    let hash = 5381;
    try {
        for (let i = 0; i < str.length; i++) {
            hash = ((hash << 5) + hash) + str.charCodeAt(i);
            /* hash * 33 + c */
        }
    }
    catch (e) {

    }
    return hash;
}

function hashStringToColor(str) {

    const hash = djb2(str);
    const r = (hash & 0xFF0000) >> 16;
    const g = (hash & 0x00FF00) >> 8;
    const b = hash & 0x0000FF;
    return "#" + ("0" + r.toString(16)).substr(-2) + ("0" + g.toString(16)).substr(-2) + ("0" + b.toString(16)).substr(-2);
}