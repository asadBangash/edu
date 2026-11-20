@php
    /** @var \Illuminate\Support\Collection $sidebar_menus */
    /** @var \App\Services\RbacService $rbac */
@endphp
<aside class="main-sidebar" id="alert2">
    @if ($rbac->hasPrivilege('student', 'can_view'))
        <form class="navbar-form navbar-left search-form2" role="search" action="{{ url('admin/admin/search') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="search_text" class="form-control search-form" placeholder="Search student">
                <span class="input-group-btn">
                    <button type="submit" name="search" id="search-btn" style="padding: 3px 12px !important;border-radius: 0px 30px 30px 0px;" class="btn btn-flat search-btn-sm"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
    @endif
    <section class="sidebar" id="sibe-box">
        <ul class="sidebar-menu verttop">
            @foreach ($sidebar_menus as $menu)
                @if ($menu->submenus->isEmpty())
                    @continue
                @endif
                <li class="treeview">
                    <a href="#">
                        <i class="{{ $menu->icon }}"></i>
                        <span>{{ __($menu->lang_key) }}</span>
                        <i class="icon-chevron-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        @foreach ($menu->submenus as $submenu)
                            <li>
                                <a href="{{ url($submenu->url) }}">
                                    <i class="icon-circle-small"></i>{{ __($submenu->lang_key) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </section>
</aside>