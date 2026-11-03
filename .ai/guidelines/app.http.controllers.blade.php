# App/Http/Controllers guidelines

- Controllers are CRUD by design: their only public methods are `index`, `create`, `store`, `show`, `edit`, `update`, and `destroy`.
- Never add a custom method (`approve`, `pin`, `follow`, `export`, `markAsRead`). Instead, find the resource being created, updated, or deleted, and give it its own controller:
  - Approving a jobPost → `JobPostApprovalController@store`. Rejecting it → `JobPostRejectionController@store`.
  - Pinning a jobPost → `JobPostPinController@update`. Unpinning it → `JobPostPinController@destroy`.
  - Following a user → `UserFollowController@store`. Unfollowing → `UserFollowController@destroy`.
  - Approving the selected jobPosts in the moderation queue → `ModerationController@update`.
- When an endpoint really does not fit CRUD, that is the sign to use an invokable controller with a single `__invoke()` method, named after the thing, minimally: `ExportController`, not `PersonalDataExportController` or `ExportPersonalDataController`.
- Controller names are nouns, never verbs: `JobPostLikeController`, not `LikeJobPostController`.
- Controllers stay thin: resolve the form request, call one action (or query), and return a response.
- Controllers never authorize or validate anything: no `abort()`, `abort_if()`, `abort_unless()`, `throw_if()`, `throw_unless()`, `$request->validate()`, `Validator::make()`, or `ValidationException`. Every check lives in the form request of that endpoint.
- `tests/Unit/ArchTest.php` enforces this.

@boostsnippet('Example controller', 'php')
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateJobPostLike;
use App\Actions\DeleteJobPostLike;
use App\Http\Requests\CreateJobPostLikeRequest;
use App\Http\Requests\DeleteJobPostLikeRequest;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final readonly class JobPostLikeController
{
    public function store(CreateJobPostLikeRequest $request, #[CurrentUser] User $user, JobPost $jobPost, CreateJobPostLike $action): RedirectResponse
    {
        $action->handle($user, $jobPost);

        return back();
    }

    public function destroy(DeleteJobPostLikeRequest $request, #[CurrentUser] User $user, JobPost $jobPost, DeleteJobPostLike $action): RedirectResponse
    {
        $action->handle($user, $jobPost);

        return back();
    }
}
@endboostsnippet