<h2>Mjesečni izvještaj (delta vrijednosti)</h2>
<p>Period: {{ $start->format('d.m.Y') }} - {{ $end->format('d.m.Y') }}</p>

@foreach($report as $companyData)
    <h3>{{ $companyData['company']->name }}</h3>
    <table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
        <thead>
            <tr>
                <th align="left">Instrument</th>
                <th align="right">Prvi unos (vrijednost)</th>
                <th align="right">Zadnji unos (vrijednost)</th>
                <th align="right">Razlika (delta)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companyData['instruments'] as $row)
                <tr>
                    <td>{{ $row['instrument']->name }}</td>
                    <td align="right">
                        @if($row['first'])
                            {{ number_format((float)$row['first']->vrijednost, 2, ',', '.') }} ({{ optional($row['first']->datum)->format('d.m.Y') }})
                        @else
                            —
                        @endif
                    </td>
                    <td align="right">
                        @if($row['last'])
                            {{ number_format((float)$row['last']->vrijednost, 2, ',', '.') }} ({{ optional($row['last']->datum)->format('d.m.Y') }})
                        @else
                            —
                        @endif
                    </td>
                    <td align="right">
                        @if(!is_null($row['delta']))
                            {{ number_format($row['delta'], 2, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nema instrumenata.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="3" align="right"><strong>UKUPNO</strong></td>
                <td align="right"><strong>{{ number_format((float)($companyData['total'] ?? 0), 2, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
@endforeach
