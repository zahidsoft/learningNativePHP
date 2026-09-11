## nativephp/plugin-speech

A NativePHP Mobile plugin

### Installation

```bash
composer require nativephp/plugin-speech
```

### PHP Usage (Livewire/Blade)

Use the `Speech` facade:

@verbatim
<code-snippet name="Using Speech Facade" lang="php">
use Nativephp\Speech\Facades\Speech;

// Execute the plugin functionality
$result = Speech::execute(['option1' => 'value']);

// Get the current status
$status = Speech::getStatus();
</code-snippet>
@endverbatim

### Available Methods

- `Speech::execute()`: Execute the plugin functionality
- `Speech::getStatus()`: Get the current status

### Events

- `SpeechCompleted`: Listen with `#[OnNative(SpeechCompleted::class)]`

@verbatim
<code-snippet name="Listening for Speech Events" lang="php">
use Native\Mobile\Attributes\OnNative;
use Nativephp\Speech\Events\SpeechCompleted;

#[OnNative(SpeechCompleted::class)]
public function handleSpeechCompleted($result, $id = null)
{
    // Handle the event
}
</code-snippet>
@endverbatim

### JavaScript Usage (Vue/React/Inertia)

@verbatim
<code-snippet name="Using Speech in JavaScript" lang="javascript">
import { speech } from '@nativephp/plugin-speech';

// Execute the plugin functionality
const result = await speech.execute({ option1: 'value' });

// Get the current status
const status = await speech.getStatus();
</code-snippet>
@endverbatim