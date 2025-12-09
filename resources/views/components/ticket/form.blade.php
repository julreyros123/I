@props(['meter' => null, 'customer' => null])
<div class="space-y-3">
    <div>
        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">Issue type <span class="text-rose-500">*</span></label>
        <select id="ticketIssueType" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" required>
            <option value="bad_installation">Bad installation</option>
            <option value="discolored_water">Discolored / dirty water</option>
            <option value="leak">Leak or pressure loss</option>
            <option value="meter_not_working">Meter not working</option>
            <option value="other">Other issue</option>
        </select>
    </div>
    <div>
        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">Describe the issue</label>
        <textarea id="ticketDescription" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" placeholder="Provide additional context"></textarea>
    </div>
    <div>
        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">Schedule follow-up</label>
        <input id="ticketSchedule" type="datetime-local" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-800 dark:text-gray-100" />
        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Optional: set a preferred visit time for the maintenance team.</p>
    </div>
</div>
