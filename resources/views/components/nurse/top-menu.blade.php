<ul class="mb-4 flex list-none flex-row flex-wrap border-b-0 pl-0 lg:justify-end justify-center">
    <li>
        <button type="button" class="lg:px-7 px-2 pt-4 pb-3.5 text-md hover:bg-neutral-100 {{ request()->routeIs('nurse.index') ? 'border-primary-600 border-b-2 text-primary-600' : 'text-neutral-600' }}">
            <i class="fa-solid fa-user-shield text-[16px] mr-2 mt-1"></i> กำลังรักษา
        </button>
    </li>
    <li>
        <button type="button" class="lg:px-7 px-2 pt-4 pb-3.5 text-md hover:bg-neutral-100 {{ request()->routeIs('nurse.index') ? 'border-primary-600 border-b-2 text-primary-600' : 'text-neutral-600' }}">
            <i class="fa-solid fa-user-plus text-[16px] mr-2 mt-1"></i> รอรับใหม่
        </button>
    </li>
    <li>
        <button type="button" class="lg:px-7 px-2 pt-4 pb-3.5 text-md hover:bg-neutral-100 {{ request()->routeIs('nurse.index') ? 'border-primary-600 border-b-2 text-primary-600' : 'text-neutral-600' }}">
            <i class="fa-solid fa-user-clock text-[16px] mr-2 mt-1"></i> รอรับย้าย
        </button>
    </li>
    <li>
        <button type="button" class="lg:px-7 px-2 pt-4 pb-3.5 text-md hover:bg-neutral-100 {{ request()->routeIs('nurse.index') ? 'border-primary-600 border-b-2 text-primary-600' : 'text-neutral-600' }}">
            <i class="fa-solid fa-user-check text-[16px] mr-2 mt-1"></i> จำหน่าย
        </button>
    </li>
</ul>
