$(document).ready(function () {

    var reorderBoardCardZIndex = function () {
        var $cards = $('#board').find('.card');
        $cards.each(function () {
            var $card = $(this);
            $card.css('z-index', parseInt($card.css('left')) + 1000);
        });
    };

    var dragStop = function (event, ui) {
        var $card = ui.draggable;
        var $parent = $card.parent();

        if ($parent.attr('id') != 'board') {
            return;
        }

        var left = ((parseInt($card.css('left')) + 50) / $parent[0].clientWidth) * 100;
        var top = ((parseInt($card.css('top')) + 70) / $parent[0].clientHeight) * 100;
        conn.send('move;' + $card.data('id') + ';' + left + ';' + top);

        reorderBoardCardZIndex();
    };

    var refreshMovable = function () {
        $(".movable").draggable({
            start: function() {
                $(this).css('z-index', 5000);
            },
            stop: function (event, ui) {
                $.extend(ui, {draggable: $(this)});
                dragStop(event, ui);
            }
        });
    };

    var tabCardAccordingToAttribute = function ($card) {
        if ($card.data('tabbed')) {
            $card.css('transform', 'rotate(90deg)');
        } else {
            $card.css('transform', 'rotate(0deg)');
        }
    };

    var refreshCommonCardEvents = function () {
        $('.commoncard').dblclick(function () {
            var $card = $(this);
            if ($card.data('tabbed')) {
                $card.data('tabbed', false);
            } else {
                $card.data('tabbed', true);
            }
            tabCardAccordingToAttribute($card);
            conn.send('tab;' + $card.data('id'));
        });
    };

    var addCardToBoard = function (code, id, type) {
        var $board = $('#board');
        var $newCard = $('<img src="img/cards/' + code + '.svg" class="movable card ' + type + '" />');
        $newCard.attr('id', 'card-' + id);
        $newCard.data('id', id);
        $newCard.data('tabbed', false);
        $newCard.appendTo($board);
        refreshCommonCardEvents();
        refreshMovable();
        reorderBoardCardZIndex();

        return $newCard;
    };

    var putCardOnDiscardPile = function ($card) {
        // Cards on discard pile can never be own cards
        if ($card.hasClass('owncard')) {
            $card.removeClass('owncard');
            $card.addClass('commoncard');
        }
        // Disable handling
        $card.removeClass('movable');
        $card.draggable('disable');
        $card.unbind('dblclick');
        // Visuals
        $card.appendTo('#discardPile');
        $card.css('left', '30px');
        $card.css('top', '10px');

        reorderBoardCardZIndex();
    };

    var conn = new WebSocket('ws://localhost:8080');
    conn.onopen = function () {
        console.log("Connection established!");

        var name = prompt('name?');
        if (typeof name !== 'undefined') {
            conn.send('name;' + name);
        }
    };

    conn.onmessage = function (e) {
        console.log('message received: ' + e.data);
        var payload = e.data.split(';');
        var command = payload[0];
        payload.splice(0, 1);

        var $newCard;
        var $opposingHand = $('#opposingHand');

        switch (command) {
            case 'lobby': {
                var name;
                var $playerList = $('#playerList');
                $playerList.html('');
                for (name of payload) {
                    name = $('<li>').html(name);
                    name.appendTo($playerList);
                }
                if (payload.length == 2) {
                    $('#startButton').show();
                }
                break;
            }
            case 'game': {
                if (payload == 'start') {
                    $('#lobby').hide();
                    $('#game').show();
                }
                break;
            }
            case 'show': {
                $opposingHand.find('.opposingcard')[0].remove();
                addCardToBoard(payload[1], payload[0], 'commoncard');
                break;
            }
            case 'move': {
                var $cardToMove = $('#card-' + payload[0]);
                $cardToMove.css('left', 'calc(' + payload[1] + '% - 50px)');
                $cardToMove.css('top', 'calc(' + payload[2] + '% - 70px)');
                reorderBoardCardZIndex();
                break;
            }
            case 'tab': {
                var $cardToTab = $('#card-' + payload[0]);
                console.log(payload[1] == true);
                $cardToTab.data('tabbed', payload[1] == true);
                tabCardAccordingToAttribute($cardToTab);
                break;
            }
            case 'packsize': {
                if (payload[0] == 0) {
                    $('#pile').hide();
                }
                break;
            }
            case 'discardsize': {
                $('#discardCounter').text(payload[0]);
                break;
            }
            case 'clearDiscardPile': {
                $('#discardCounter').text(0);
                $('#discardPile').find('.card').remove();
                break;
            }
            case 'draw': {
                if (payload[0] == 'opposing') {
                    var $opposingCard = $('<img src="img/cards/back.svg" style="width: 100px; height: 140px;" class="opposingcard" />');
                    $opposingCard.appendTo($opposingHand);
                    break;
                }
                var $ownHand = $('#ownHand');
                $newCard = addCardToBoard(payload[1], payload[0], 'owncard');
                $newCard.css('top', $ownHand.position().top + 'px');

                var $ownCards = $('.owncard');
                if ($ownCards.length > 1) {
                    var mostRight;
                    $ownCards.each(function (index, card) {
                        var $card = $(card);
                        if (mostRight == null || $card.position().left > mostRight.position().left) {
                            mostRight = $card;
                        }
                    });

                    $newCard.css('left', (mostRight.position().left + 50) + 'px');
                }

                refreshMovable();
                reorderBoardCardZIndex();
                break;
            }
            case 'discard': {
                var $cardToDiscard = $('#card-' + payload[0]);
                // Card was on board and was not discarded directly from hand
                if ($cardToDiscard.length > 0) {
                    putCardOnDiscardPile($cardToDiscard);
                } else {
                    addCardToBoard(payload[1], payload[0], 'commoncard');
                    $cardToDiscard = $('#card-' + payload[0]);
                    putCardOnDiscardPile($cardToDiscard);
                    $opposingHand.find('.opposingcard')[0].remove();
                }
                break;
            }
            case 'chat': {
                let now = new Date();
                let prefix = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds() + ' ';
                $('<p></p>').text(prefix + payload.join(';')).appendTo('#messages');
                let $messages = $('#messages');
                $messages.scrollTop($messages[0].scrollHeight);
                break;
            }
        }
    };

    $('#startButton').click(function () {
        conn.send('start');
    });

    $('#pile').click(function () {
        conn.send('draw');
    });

    $('#table').droppable({
        drop: function (event, ui) {

            var $card = ui.draggable;

            if ($card.hasClass('owncard')) {
                $card.removeClass('owncard');
                $card.addClass('commoncard');

                conn.send('show;' + $card.data('id'));
                dragStop(event, ui);
                refreshCommonCardEvents();
            }
        }
    });

    $('#discardPile').droppable({
        greedy: true,
        drop: function (event, ui) {
            var $card = ui.draggable;
            putCardOnDiscardPile($card);
            conn.send('discard;' + $card.data('id'));
        }
    });

    $('#grabDiscardPileButton').click(function () {
        conn.send('pickupDiscardPile');
    });

    $('#chatInput').keydown(function(event) {
        if (event.which == 13) {
            let inputText = $(this).val();
            if (inputText) {
                conn.send('chat;' + $(this).val());
                $(this).val('');
            }
        }
    });

});