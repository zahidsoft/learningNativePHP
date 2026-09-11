# Speech Plugin for NativePHP Mobile

A NativePHP Mobile plugin

## Installation

```bash
composer require nativephp/plugin-speech
```

## Usage

```php
use Nativephp\Speech\Facades\Speech;

// Execute functionality
$result = Speech::execute(['option1' => 'value']);

// Get status
$status = Speech::getStatus();
```

## Listening for Events

```php
use Livewire\Attributes\On;

#[On('native:Nativephp\Speech\Events\SpeechCompleted')]
public function handleSpeechCompleted($result, $id = null)
{
    // Handle the event
}
```

## License

MIT