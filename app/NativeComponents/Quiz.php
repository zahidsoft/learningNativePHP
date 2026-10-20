<?php

namespace App\NativeComponents;

use App\Actions\Learning\MakeQuizQuestion;
use App\Actions\Learning\PlayLearningSound;
use App\Actions\Learning\RecordProgress;
use App\Enums\ProgressKind;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Quiz extends NativeComponent
{
    public const QUESTION_COUNT = 10;

    /**
     * @var array{category: string, character: string, font: string, answer: string, options: list<string>}
     */
    public array $question = ['category' => '', 'character' => '', 'font' => 'glyph', 'answer' => '', 'options' => []];

    public int $questionNumber = 1;

    public int $score = 0;

    public ?string $picked = null;

    public bool $isFinished = false;

    public function mount(): void
    {
        $this->restart();
    }

    public function render(): Element
    {
        return $this->view('quiz', [
            'question' => $this->question,
            'questionNumber' => $this->questionNumber,
            'questionCount' => self::QUESTION_COUNT,
            'score' => $this->score,
            'picked' => $this->picked,
            'isCorrect' => $this->picked === $this->question['answer'],
            'isFinished' => $this->isFinished,
        ]);
    }

    /**
     * Answer the current question with the option at the given position.
     */
    public function pick(int $position): void
    {
        $option = $this->question['options'][$position] ?? null;

        if ($this->picked !== null || $option === null) {
            return;
        }

        $this->picked = $option;
        $isCorrect = $option === $this->question['answer'];

        if ($isCorrect) {
            $this->score++;
        }

        app(PlayLearningSound::class)->quizFeedback($isCorrect);
        app(RecordProgress::class)->answered(ProgressKind::Letter, $this->question['category'], $this->question['character'], $isCorrect);
    }

    /**
     * Move on to the next question, or to the result once the last is answered.
     */
    public function next(): void
    {
        if ($this->picked === null) {
            return;
        }

        if ($this->questionNumber >= self::QUESTION_COUNT) {
            $this->isFinished = true;

            return;
        }

        $this->questionNumber++;
        $this->picked = null;
        $this->question = app(MakeQuizQuestion::class)->handle();
    }

    /**
     * Start a fresh round from the first question.
     */
    public function restart(): void
    {
        $this->questionNumber = 1;
        $this->score = 0;
        $this->picked = null;
        $this->isFinished = false;
        $this->question = app(MakeQuizQuestion::class)->handle();
    }
}
