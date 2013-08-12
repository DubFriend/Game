var $gameWindow = $('#game-window'),
    $canvas = $('<canvas id="game-screen" width="' + $gameWindow.width() +
                '" height="' + $gameWindow.height() + '"></canvas>'),
    draw = newDraw({
        context: $canvas[0].getContext('2d'),
        width: $canvas.width(),
        height: $canvas.height()
    });

$gameWindow.append($canvas);
draw.circle(100, 100, 25, 'blue');
