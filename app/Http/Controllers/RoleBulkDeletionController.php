<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Role\DeleteBulkRoles;
use App\Http\Requests\Role\DeleteBulkRoleRequest;
use Illuminate\Http\RedirectResponse;

use function back;

final class RoleBulkDeletionController
{
    /**
     * Remove multiple specified roles from storage.
     */
    public function destroy(DeleteBulkRoleRequest $request, DeleteBulkRoles $deleteBulkRoles): RedirectResponse
    {
        /** @var array<int, int|string> $ids */
        $ids = $request->validated('ids');

        $deleteBulkRoles->handle($ids);

        return back();
    }
}
