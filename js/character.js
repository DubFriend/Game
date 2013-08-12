//base class for all characters in the game.
var createCharacter = function (fig) {
    'use strict';
    fig = fig || {};
    var that = {},
        coord = fig.coord || { x: 5, y: 5 },
        speed = fig.speed || { x: 0, y: 0 },
        size = fig.size || 5;

    that.draw = fig.draw || function () {};

    that.coord = function () { return coord; };
    that.speed = function () { return speed; };
    that.size = function () { return size; };

    that.intersect = function (testCoord) {
        return ( Math.abs(coord.x - testCoord.x) <= size &&
                 Math.abs(coord.y - testCoord.y) <= size );
    };

    //move character forward one game cycle.
    that.tick = function () {
        coord.x += speed.x;
        coord.y += speed.y;
    };

    that.accelerate = function (diff) {
        speed.x += diff.x;
        speed.y += diff.y;
    };

    return that;
};
