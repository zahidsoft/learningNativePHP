<?php

use App\Actions\Learning\MakeQuizQuestion;
use App\NativeComponents\Quiz;
use Native\Mobile\Testing\Native;
use Native\Mobile\Testing\TestableComponent;

/**
 * Open the quiz on a known question: the letter "B", answered by "Bee".
 */
function quizOnLetterB(): TestableComponent
{
    return Native::visit('/quiz')->set('question', [
        'character' => 'B',
        'font' => 'glyph',
        'answer' => 'Bee',
        'options' => ['Ay', 'Bee', 'See', 'Dee'],
    ]);
}

test('it starts on the first question with no score', function () {
    Native::visit('/quiz')
        ->assertScreen(Quiz::class)
        ->assertSee('Question 1 of 10')
        ->assertSet('score', 0)
        ->assertSet('picked', null);
});

test('it scores a correct answer and says so', function () {
    quizOnLetterB()
        ->tap('Bee')
        ->assertSet('score', 1)
        ->assertSee('Correct! Well done')
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'Correct');
});

test('it does not score a wrong answer and reveals the right one', function () {
    quizOnLetterB()
        ->tap('See')
        ->assertSet('score', 0)
        ->assertSee('It was Bee')
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'Try again');
});

test('it ignores a second answer to the same question', function () {
    quizOnLetterB()
        ->tap('See')
        ->tap('Bee')
        ->assertSet('picked', 'See')
        ->assertSet('score', 0);
});

test('it does not move on before the question is answered', function () {
    quizOnLetterB()
        ->call('next')
        ->assertSet('questionNumber', 1);
});

test('it moves on to a fresh question after an answer', function () {
    quizOnLetterB()
        ->tap('Bee')
        ->tap('Next')
        ->assertSet('questionNumber', 2)
        ->assertSet('picked', null)
        ->assertSet('score', 1);
});

test('it shows the result after the last question', function () {
    quizOnLetterB()
        ->set('questionNumber', 10)
        ->set('score', 6)
        ->tap('Bee')
        ->tap('Finish')
        ->assertSet('isFinished', true)
        ->assertSee('Great job!')
        ->assertSee('You got 7 out of 10');
});

test('it starts a fresh round from the result', function () {
    quizOnLetterB()
        ->set('questionNumber', 10)
        ->tap('See')
        ->tap('Finish')
        ->assertSee('Good try!')
        ->tap('Play again')
        ->assertSet('isFinished', false)
        ->assertSet('questionNumber', 1)
        ->assertSet('score', 0)
        ->assertSee('Question 1 of 10');
});

test('it returns to the previous screen from the result', function () {
    quizOnLetterB()
        ->set('questionNumber', 10)
        ->tap('Bee')
        ->tap('Finish')
        ->tap('Back to learning')
        ->assertWentBack();
});

test('a question offers the right name among four different names', function () {
    $question = app(MakeQuizQuestion::class)->handle();

    expect($question['options'])->toHaveCount(4)
        ->toContain($question['answer'])
        ->and(array_unique($question['options']))->toHaveCount(4);
});
