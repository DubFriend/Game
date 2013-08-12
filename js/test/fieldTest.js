(function () {
'use strict';

var field, character, drawCount;

module('field', {
    setup: function () {
        drawCount = 0;
        character = createCharacter({
            coord: { x: 5, y: 5 },
            speed: { x: 1, y: 1 },
            size: 5,
            draw: function () {
                drawCount += 1;
            }
        });

        field = createField({
            size: {
                width: 100,
                height: 200
            },
            items: { foo: character }
        });
    }
});

test('add', function () {
    field.add('key', 'newItem');
    deepEqual(field._getItems(), { foo: character, key: 'newItem' }, 'item added');
    throws(
        function() {
            field.add('key', 'overwrite');
        },
        'item with that key allready exists'
    );
});

test('tick', function () {
    field.tick();
    deepEqual(character.coord(), { x: 6, y: 6 }, 'character is ticked');
    deepEqual(drawCount, 1, 'draw is called on character');
});

}());
