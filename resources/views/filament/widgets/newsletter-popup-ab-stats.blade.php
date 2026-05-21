<x-filament-widgets::widget>
    <x-filament::section heading="Teste A/B do popup">
        <p class="tw-mb-4 tw-text-center tw-text-sm tw-text-gray-500">
            {{ $periodLabel }}
        </p>

        <div class="tw-flex tw-justify-center">
            <table class="tw-text-sm" style="border-collapse: separate; border-spacing: 0.75rem 0.5rem;">
                <thead>
                    <tr class="tw-text-gray-500">
                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center tw-font-medium">Variante</th>
                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center tw-font-medium">Viu o popup</th>
                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center tw-font-medium">Inscreveu-se</th>
                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center tw-font-medium">Taxa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="tw-px-4 tw-py-2 tw-text-center tw-font-semibold">{{ $row['variant'] }}</td>
                            <td class="tw-px-4 tw-py-2 tw-text-center">{{ number_format($row['impressions'], 0, ',', '.') }}</td>
                            <td class="tw-px-4 tw-py-2 tw-text-center">{{ number_format($row['conversions'], 0, ',', '.') }}</td>
                            <td class="tw-px-4 tw-py-2 tw-text-center">
                                {{ $row['rate'] !== null ? $row['rate'].'%' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="tw-py-4 tw-text-center tw-text-gray-500">
                                Ainda não há dados do popup neste período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
