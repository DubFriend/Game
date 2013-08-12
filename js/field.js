//game field holds all characters, and other items, and current map state.
//proccesses game level events and logic.
var createField = function (fig) {
    'use strict';
    fig = fig || {};

    var that = {},
        size = fig.size,
        items = fig.items || {};

    that.add = function (key, item) {
        if(!items[key]) {
            items[key] = item;
        }
        else {
            throw 'item with that key allready exists';
        }
    };

    //intended for testing purposes only
    that._getItems = function () {
        return items;
    };

    that.remove = function (key) {
        delete items[key];
    };

    //move game state forward one game cycle.
    that.tick = function () {
        _.each(items, function (item) {
            item.tick();
            item.draw();
        });
    };

    return that;
};
