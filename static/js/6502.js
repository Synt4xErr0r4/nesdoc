function _loadAddrModes_processNotes(name, entry, notes) {
    for(let i = notes.length; i > 0; --i)
        entry = entry.replace('*'.repeat(i), `%%${i}%%`);

    for(let i = 0; i < notes.length; ++i)
        entry = entry.replace(
            `%%${i + 1}%%`,
            `<abbr title="${notes[i]}">${'*'.repeat(i + 1)}</abbr>`
        );
    
    return entry;
}

function _loadAddrModes_populate(name, table, data, notes) {
    let head = $('<thead>');

    head.append(
        '<th>Cycle</th>' +
        '<th>Value</th>' +
        '<th>Bus</th>' +
        '<th>Comment</th>'
    )

    let body = $('<tbody>');

    data.forEach(entry => {
        let tr = $('<tr>');

        let cycle = $('<td>');
        let value = $('<td>');
        let bus = $('<td>');
        let comment = $('<td>');

        [ cycle, value, bus, comment ].forEach((td, idx) =>
            td.append(_loadAddrModes_processNotes(name, entry[idx], notes))
        )

        tr.append(cycle);
        tr.append(value);
        tr.append(bus);
        tr.append(comment);

        body.append(tr);
    });

    table.append(head);
    table.append(body);
}

function loadAddrModes() {
    $.getJSON("/static/data/timings.json", data => {
        const normal = data.normal;
        const special = data.special;

        Object.keys(normal).forEach(addrmode => {
            let obj = normal[addrmode];

            let list = $(`#addrmod-instr-${addrmode}`);
            let table = $(`#addrmod-table-${addrmode}`);
            let notes = $(`#addrmod-notes-${addrmode}`);

            _loadAddrModes_populate(addrmode, table, obj.cycles, obj.notes);

            obj.notes.forEach((note, idx) => {
                let p = $('<span>');

                p.attr('id', `addrmode-note-${addrmode}-${idx}`);
                p.append('*'.repeat(idx + 1) + ' ' + note);
                p.append('<br>');

                notes.append(p);
            });

            let instructions = [];

            obj.instructions.forEach(instr =>
                instructions.push(`<a href="#${instr}">${instr}</a>`)
            )

            list.append(instructions.join(', '));
        })

        Object.keys(special).forEach(instruction =>
            _loadAddrModes_populate(instruction, $(`#addrmod-table-${instruction.toLowerCase()}`), special[instruction], [])
        )
    });
}

function _loadInstrTable_instr(entry) {
    let row = $('<tr>');
    row.attr('id', entry.mnemonic);

    let mn = $('<td>');
    mn.addClass('instr-mnemonic');
    mn.text(entry.mnemonic);

    let op = $('<td>');
    op.addClass('instr-operation');

    let code = $('<code>');
    code.text(entry.operation);
    op.append(code);

    let flags = $('<td>');
    flags.addClass('instr-flags');

    let pre = $('<code>');
    pre.text(entry.flags);
    flags.append(pre);

    row.append(mn);
    row.append(op);
    row.append(flags);

    return row;
}

function loadInstrTable() {
    $.getJSON("/static/data/instructions.json", json => {
        const data = json.data;

        let table = $(`#matrix-table`);
        let body = $('<tbody>');
        let header = $('<tr>');

        body.append(header);

        header.append('<th></th>');

        const modes = {
            imp: '',
            imm: '#v',
            ind: '(a)',
            rel: 'label',
            abs: 'a',
            abx: 'a,X',
            aby: 'a,Y',
            zpg: 'd',
            zpx: 'd,X',
            zpy: 'd,Y',
            izx: '(d,X)',
            izy: '(d),Y',
        };
        const types = {
            '': 'generic',
            r: 'read',
            m: 'rmw',
            w: 'write'
        };
        const bytes = {
            imp: 1,
            imm: 2,
            ind: 3,
            rel: 2,
            abs: 3,
            abx: 3,
            aby: 3,
            zpg: 2,
            zpx: 2,
            zpy: 2,
            izx: 2,
            izy: 2,
        };

        let officialOps = {};
        let unofficialOps = {};

        for(let i = 0; i < 16; ++i) {
            header.append(`<th>x${i.toString(16).toUpperCase()}</th>`);

            let row = $('<tr>');
            row.append(`<th>${i.toString(16).toUpperCase()}x</th>`);

            for(let j = 0; j < 16; ++j) {
                let idx = j | (i << 4);
                let entry = data[idx];

                let cell = $('<td>');

                let anchor = $('<a>');
                anchor.attr('href', `#${entry.mnemonic}`);

                let modeName = entry.addrmode.substring(0, 3);
                let modeType = entry.addrmode.substring(3);

                if(entry.mnemonic === 'KIL') {
                    modeName = 'imp';
                    modeType = '';
                }

                let mnemonic = $('<span>');
                mnemonic.addClass('matrix-mnemonic');
                mnemonic.addClass(`matrix-mnemonic-${entry.mnemonic}`);
                mnemonic.text(entry.mnemonic);

                let mode = $('<span>');
                mode.addClass(`matrix-mode`);
                mode.addClass(`matrix-mode-${modeName}`);
                mode.text(modes[modeName]);

                anchor.append(mnemonic);
                anchor.append(mode);

                if(entry.stability.length > 0) {
                    cell.addClass('matrix-illegal');

                    if(entry.stability !== 'stable') {
                        let stability = $('<abbr>');
                        stability.addClass('matrix-stability');

                        if(entry.stability === 'unstable') {
                            stability.text('!')
                            stability.attr('title', 'unstable');
                        }
                        else {
                            stability.text('!!')
                            stability.attr('title', 'highly unstable');
                        }

                        anchor.append(stability);
                    }

                    if(!(entry.mnemonic in unofficialOps))
                        unofficialOps[entry.mnemonic] = entry

                    anchor.attr('href', `#${entry.mnemonic}-unoff`);
                }
                else if(!(entry.mnemonic in officialOps))
                    officialOps[entry.mnemonic] = entry

                cell.append(anchor);

                let cycles = $('<span>');
                cycles.addClass(`matrix-cycles`);
                cycles.addClass(`matrix-cycles-${entry.cycles}`);
                cycles.text(entry.cycles);

                let bytxs = $('<span>');
                bytxs.addClass(`matrix-bytes`);
                bytxs.addClass(`matrix-bytes-${bytes[modeName]}`);
                bytxs.text(bytes[modeName]);

                cell.append('<br>');
                cell.append(cycles);
                cell.append(bytxs);

                cell.data('6502-mnemonic', entry.mnemonic);
                cell.data('6502-mode', modeName);
                cell.data('6502-type', modeType);
                cell.data('6502-cycles', entry.cycles);

                cell.addClass(`matrix-type-${types[modeType]}`);
                cell.addClass('matrix-entry');

                row.append(cell);
            }

            body.append(row);
        }

        table.append(body);

        let tableOfficial = $('#instr-official');
        
        body = tableOfficial.find('tbody');

        Object.keys(officialOps).sort().forEach(mnemonic =>
            body.append(_loadInstrTable_instr(officialOps[mnemonic]))
        )

        let tableUnofficial = $('#instr-unofficial');

        body = tableUnofficial.find('tbody');

        Object.keys(unofficialOps).sort().forEach(mnemonic => {
            let entry = unofficialOps[mnemonic];

            let row = _loadInstrTable_instr(entry)
            row.attr('id', `${entry.mnemonic}-unoff`);

            let stability = $('<td>')
            stability.append('instr-stability');
            stability.text(entry.stability);

            row.append(stability);

            body.append(row)
        })
    })
}

$(() => {
    
    let matrices = {
        normal:  $('#matrix-table-normal'),
        grouped: $('#matrix-table-grouped'),
        pattern: $('#matrix-table-pattern')
    };

    loadAddrModes();
    loadInstrTable();
})