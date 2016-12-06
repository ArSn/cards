$(document).ready(function () {

    const conn = new WebSocket('ws://' + window.location.hostname + ':8080');
    let localPlayerName;

    const reorderBoardCardZIndex = function () {
        const $cards = $('#board').find('.card');
        $cards.each(function () {
            const $card = $(this);
            $card.css('z-index', (parseInt($card.css('left')) * parseInt($card.css('top'))) + 1000);
        });
    };

    const findCardCoordinates = function($card) {

        const $parent = $card.parent();

        let left = ((parseInt($card.css('left'))) / $parent[0].clientWidth) * 100;
        let top = ((parseInt($card.css('top')) + 100) / $parent[0].clientHeight) * 100;

        return {
            left: left,
            top: top
        };
    };

    const dragStop = function (event, ui) {
        const $card = ui.draggable;
        const $parent = $card.parent();

        if ($parent.attr('id') != 'board') {
            return;
        }

        const coords = findCardCoordinates($card);
        conn.send('move;' + $card.data('id') + ';' + coords.left + ';' + coords.top);

        reorderBoardCardZIndex();
    };

    const refreshMovable = function () {
        $(".movable").draggable({
            start: function() {
                $(this).css('z-index', 1500000);
            },
            stop: function (event, ui) {
                $.extend(ui, {draggable: $(this)});
                dragStop(event, ui);
            },
            grid: [ 20, 20 ]
        });
    };

    const tabCardAccordingToAttribute = function ($card) {
        if ($card.data('tabbed')) {
            $card.css('transform', 'rotate(90deg)');
        } else {
            $card.css('transform', 'rotate(0deg)');
        }
    };

    const refreshCommonCardEvents = function () {
        $('.commoncard').dblclick(function () {
            const $card = $(this);
            if ($card.data('tabbed')) {
                $card.data('tabbed', false);
            } else {
                $card.data('tabbed', true);
            }
            tabCardAccordingToAttribute($card);
            conn.send('tab;' + $card.data('id'));
        });
    };

    const addCardToBoard = function (code, id, type) {
        const $board = $('#board');
        const $newCard = $('<img src="img/cards/' + code + '.svg" class="movable card ' + type + '" />');
        $newCard.attr('id', 'card-' + id);
        $newCard.data('id', id);
        $newCard.data('tabbed', false);
        $newCard.appendTo($board);
        refreshCommonCardEvents();
        refreshMovable();
        reorderBoardCardZIndex();

        return $newCard;
    };

    const putCardOnDiscardPile = function ($card) {
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

    const askForName = function() {
        const name = prompt('name?');
        if (name) {
            return name;
        }
        return askForName();
    };

    conn.onopen = function () {
        console.log("Connection established!");

        localPlayerName = askForName();
        conn.send('name;' + localPlayerName);
    };

    conn.onmessage = function (e) {
        console.log('message received: ' + e.data);
        let payload = e.data.split(';');
        const command = payload[0];
        payload.splice(0, 1);

        const $opposingHand = $('#opposingHand');

        switch (command) {
            case 'lobby': {
                const $playerList = $('#playerList');
                $playerList.html('');
                for (let name of payload) {
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
                if (payload == 'end') {
                    alert('The game has ended. You will now be redirected to the lobby.');
                    window.location.href = '/';
                }
                break;
            }
            case 'show': {
                $opposingHand.find('.opposingcard')[0].remove();
                addCardToBoard(payload[1], payload[0], 'commoncard');
                break;
            }
            case 'move': {
                const $cardToMove = $('#card-' + payload[0]);
                $cardToMove.animate({
                    left: payload[1] + '%',
                    top: payload[2] + '%'
                }, {
                    complete: reorderBoardCardZIndex
                });
                break;
            }
            case 'tab': {
                const $cardToTab = $('#card-' + payload[0]);
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
                    const $opposingCard = $('<img src="img/cards/back.svg" class="card opposingcard" />');
                    $opposingCard.appendTo($opposingHand);
                    break;
                }
                const $ownHand = $('#ownHand');
                const $newCard = addCardToBoard(payload[1], payload[0], 'owncard');
                $newCard.css('top', $ownHand.position().top + 'px');

                const $ownCards = $('.owncard');
                if ($ownCards.length > 1) {
                    let mostRight;
                    $ownCards.each(function (index, card) {
                        const $card = $(card);
                        if (mostRight == null || $card.position().left > mostRight.position().left) {
                            mostRight = $card;
                        }
                    });

                    $newCard.css('left', (mostRight.position().left + 20) + 'px');
                }

                refreshMovable();
                reorderBoardCardZIndex();
                break;
            }
            case 'discard': {
                //noinspection JSJQueryEfficiency
                let $cardToDiscard = $('#card-' + payload[0]);
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
            case 'currentPlayer': {
                let $status = $('#status');
                let $content = $('<span />');
                if (payload[0] == localPlayerName) {
                    $content.text('Your turn, ' + localPlayerName + '! ');
                    $('<a />').text('Done').click(function() {
                        conn.send('endTurn');
                    }).appendTo($content);
                    $status.addClass('ownTurn');
                    $status.removeClass('opposingTurn');
                } else {
                    $content.text(payload[0] + 's turn. ');
                    $status.addClass('opposingTurn');
                    $status.removeClass('ownTurn');
                }
                $status.html('');
                $content.appendTo($status);
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

            const $card = ui.draggable;

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
            let $card = ui.draggable;
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