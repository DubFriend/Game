//class for drawing to the canvas.
var newDraw = function (fig) {
    var ctx = fig.context,
        WIDTH = fig.width,
        HEIGHT = fig.height,
        defaultColor = fig.defaultColor || 'yellow',
        path = function (callback) {
            ctx.beginPath();
            callback();
            ctx.closePath();
        },
        circlePath = function (x, y, size) {
            path(function () {
                ctx.arc(x, y, size, 0, Math.PI * 2, true);
            });
        };

    return {
        disc: function (x, y, size, color) {
            ctx.fillStyle = color || defaultColor;
            circlePath(x, y, size);
            ctx.fill();
        },
        circle: function (x, y, size, color) {
            ctx.strokeStyle = color || defaultColor;
            circlePath(x, y, size);
            ctx.stroke();
        },
        image: function (x, y, image) {
            ctx.drawImage(image, x, y);
        },
        clear: function () {
            ctx.clearRect(0, 0, WIDTH, HEIGHT);
        }
    };
};
