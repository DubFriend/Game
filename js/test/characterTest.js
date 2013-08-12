(function () {
'use strict';

var character, drawCoord;

module('character', {
    setup: function () {
        drawCoord = null;
        character = createCharacter({
            coord: { x: 20, y: 30 },
            size: 3,
            speed: { x: 10, y: 15 },
            draw: function () {
                drawCoord = this.coord();
            }
        });
    }
});

test('intersect', function () {
    ok(character.intersect({ x: 23, y: 33 }), 'success');
    ok(!character.intersect({ x: 20, y: 34 }), 'failure - y');
    ok(!character.intersect({ x: 16, y: 30 }), 'failure - x');
});

test('accelerate', function () {
    character.accelerate({ x: -1, y: 2 });
    deepEqual(character.speed(), { x: 9, y: 17 });
});

test('tick', function () {
    character.tick();
    deepEqual(character.coord(), { x: 30, y: 45 });
});

test('draw', function () {
    character.draw();
    deepEqual(drawCoord, {x: 20, y: 30 }, 'draw function is called and bound to calling object');
});


}());
