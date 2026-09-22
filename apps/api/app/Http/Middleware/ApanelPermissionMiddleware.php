<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApanelPermissionMiddleware
{
    protected array $resourcePermissions = [
        'locales' => 'manage-locales',
        'translation-keys' => 'manage-translations',
        'translation-values' => 'manage-translations',
        'settings' => 'manage-settings',
        'menus' => 'manage-menus',
        'menu-items' => 'manage-menus',
        'faculties' => 'manage-faculties',
        'departments' => 'manage-departments',
        'programs' => 'manage-programs',
        'courses' => 'manage-courses',
        'news' => 'manage-news',
        'blogs' => 'manage-news',
        'blog-departments' => 'manage-news',
        'newsletter-subscriptions' => 'manage-inquiries',
        'announcements' => 'manage-announcements',
        'administration-profiles' => 'manage-staff',
        'staff' => 'manage-staff',
        'services' => 'manage-services',
        'videos' => 'manage-news',
        'blog-comments' => 'manage-comments',
        'video-comments' => 'manage-comments',
        'media' => 'manage-media',
        'users' => 'manage-users',
        'roles' => 'manage-roles',
        'permissions' => 'manage-permissions',
        'students' => 'manage-students',
        'applications' => 'manage-applications',
        'countries' => 'manage-applications',
        'nationalities' => 'manage-applications',
        'document-requirements' => 'manage-documents',
        'application-documents' => 'manage-documents|manage-applications',
        'contracts' => 'manage-contracts',
        'payments' => 'manage-payments',
        'inquiries' => 'manage-inquiries',
        'support-tickets' => 'manage-support-tickets',
        'comments' => 'manage-comments',
        'notifications' => 'manage-notifications',
        'audit-logs' => 'view-audit-logs',
        'green-campus-stats' => 'manage-pages',
        'green-campus-articles' => 'manage-pages',
        'application-status-histories' => 'manage-applications',
        'university-centers' => 'manage-services',
    ];

    protected array $cmsPermissions = [
        'news-events' => 'manage-news',
        'announcements' => 'manage-announcements',
        'blog' => 'manage-news',
        'video-bdtu' => 'manage-news',
        'interactive-services' => 'manage-services',
        'university-centers' => 'manage-services',
        'green-campus' => 'manage-pages',
        'administration' => 'manage-pages',
        'footer-web' => 'manage-pages',
        'about-page' => 'manage-pages',
        'apply-page' => 'manage-pages',
        'contact-page' => 'manage-pages',
        'home' => 'manage-pages',
        'faculty-page' => 'manage-pages',
        'department-page' => 'manage-pages',
        'programs' => 'manage-pages',
        'cms-auth' => 'manage-pages',
        'header-navbar' => 'manage-pages',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasRole('apanel') || $user->hasRole('super_admin')) {
            return $next($request);
        }

        $requiredPermissions = $this->requiredPermissions($request);

        if ($requiredPermissions === []) {
            throw new AuthorizationException('Unauthorized.');
        }

        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role')) {
            throw new AuthorizationException('Unauthorized.');
        }

        foreach ($requiredPermissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        throw new AuthorizationException('Unauthorized.');
    }

    protected function requiredPermissions(Request $request): array
    {
        $segments = explode('/', trim($request->path(), '/'));
        $apanelIndex = array_search('apanel', $segments, true);

        if ($apanelIndex === false) {
            return [];
        }

        $resource = $segments[$apanelIndex + 1] ?? '';
        $subResource = $segments[$apanelIndex + 2] ?? '';

        if ($resource === 'applications-workflow') {
            if (
                in_array('documents', $segments, true)
                || in_array('application-documents', $segments, true)
            ) {
                return ['manage-documents', 'manage-applications'];
            }

            if (
                in_array('payments', $segments, true)
                || in_array('contract-payments', $segments, true)
                || in_array('service-fees', $segments, true)
            ) {
                return ['manage-payments', 'manage-applications'];
            }

            return ['manage-applications'];
        }

        if ($resource === 'application-documents') {
            return ['manage-documents', 'manage-applications'];
        }

        if ($resource === 'newsletter') {
            return ['manage-inquiries'];
        }

        if ($resource === 'cms') {
            return $this->splitPermissions($this->cmsPermissions[$subResource] ?? null);
        }

        return $this->splitPermissions($this->resourcePermissions[$resource] ?? null);
    }

    protected function splitPermissions(?string $permissions): array
    {
        if (! $permissions) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode('|', $permissions))));
    }
}
