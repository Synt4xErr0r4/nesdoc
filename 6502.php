<?php
$pageInfo = [ 'title' => 'NES CPU 6502', 'script' => '6502' ];
require_once 'private/header.php';
?>

<h1>NES CPU: 6502</h1>

<hr>

<h2 id="registers">Registers</h2>

<table class="table">
    <tbody>
        <tr>
            <th>Abbrevations</th>
            <td id="reg-A">A</td>
            <td id="reg-X">X</td>
            <td id="reg-Y">Y</td>
            <td id="reg-S">S</td>
            <td id="reg-P">P</td>
            <td id="reg-PC">PC</td>
        </tr>
        <tr>
            <th>Names</th>
            <td>Accumulator</td>
            <td>X Register</td>
            <td>Y Register</td>
            <td>Stack Pointer</td>
            <td>Status Register</td>
            <td>Program Counter</td>
        </tr>
        <tr>
            <th>Size</th>
            <td>8-bit</td>
            <td>8-bit</td>
            <td>8-bit</td>
            <td>8-bit</td>
            <td>8-bit</td>
            <td>16-bit</td>
        </tr>
        <tr>
            <th>Comments</th>
            <td>general purpose, ALU</td>
            <td>general purpose, index register</td>
            <td>general purpose, index register</td>
            <td>used to push to/ pull from tde stack</td>
            <td>contains several flags; see <a href="#flags">below</a></td>
            <td>address of the current instruction</td>
        </tr>
    </tbody>
</table>

<h2 id="flags">Flags</h2>

<table class="table">
    <tbody>
        <tr>
            <th>Bit</th>
            <td>7</td>
            <td>6</td>
            <td>5</td>
            <td>4</td>
            <td>3</td>
            <td>2</td>
            <td>1</td>
            <td>0</td>
        </tr>
        <tr>
            <th>Abbrevation</th>
            <td id="flag-N">N</td>
            <td id="flag-V">V</td>
            <td>-</td>
            <td id="flag-B">B</td>
            <td id="flag-D">D</td>
            <td id="flag-I">I</td>
            <td id="flag-Z">Z</td>
            <td id="flag-C">C</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>Negative</td>
            <td>Overflow</td>
            <td>Unused</td>
            <td>Unused</td>
            <td>Decimal</td>
            <td>Interrupt Disable</td>
            <td>Zero</td>
            <td>Carry</td>
        </tr>
        <tr>
            <th>Set/Reset</th>
            <td></td>
            <td><a href="#CLV">CLV</a></td>
            <td></td>
            <td></td>
            <td><a href="#SED">SED</a>, <a href="#CLD">CLD</a></td>
            <td><a href="#SEI">SEI</a>, <a href="#CLI">CLI</a></td>
            <td></td>
            <td><a href="#SEC">SEC</a>, <a href="#CLC">CLC</a></td>
        </tr>
        <tr>
            <th>Comment</th>
            <td>bit <code>7</code> of the result of an operation (sign bit)</td>
            <td>set by <a href="#ADC">ADC</a> and <a href="#SBC">SBC</a> if signed result would have overflown</td>
            <td>unused; always <code>1</code></td>
            <td>
                technically unused; but when P is pushed to the stack, this bit may be either
                <code>0</code> or <code>1</code>, depending on the instruction:
                <ul>
                    <li>
                        <a href="#PHP">PHP</a> and <a href="#BRK">BRK</a>:
                        <code>1</code>
                    </li>
                    <li>
                        <a href="#int-IRQ">IRQ</a> and <a href="#int-NMI">NMI</a>:
                        <code>0</code>
                    </li>
                </ul>
                <i>Note:</i> when <a href="#reg-P">P</a> is pushed, the
                <a href="#flag-I">I</a> flag is set as well (unless the operation was <a href="#PHP">PHP</a>)
            </td>
            <td>no effect on the NES</td>
            <td>
                when set, all <a href="#int">interrupts</a> except <a href="#int-NMI">NMI</a> are inhibited;<br>
                automatically set when an <a href="#int-IRQ">IRQ</a> occurs, and restored by the interrupt handler's <a href="#RTI">RTI</a><br>
                clearing this flag when an <a href="#int-IRQ">IRQ</a> is pending causes the interrupt to trigger immediately
            </td>
            <td>set if the result of an operation is equal to <code>0</code></td>
            <td>
                set to the carry of the addition by <a href="#ADC">ADC</a>;<br>
                set if no borrow was the result ("greater than or equal to") by <a href="#SBC">SBC</a> and <a href="#CMP">CMP</a>;<br>
                set to the bit shifted out by <a href="#ASL">ASL</a>, <a href="LSR">LSR</a>, <a href="#ROL">ROL</a>, and <a href="#ROR">ROR</a>
            </td>
        </tr>
    </tbody>
</table>

<a href="/static/data/instructions.json" download="instructions.json" class="btn btn-primary">Download as JSON</a>
<a href="/static/data/instructions.csv" download="instructions.csv" class="btn btn-primary">Download as CSV</a>

<!-- TODO
<hr>

<div>
    <div class="input-group mb-3">
        <span class="input-group-text">Filter</span>
        <input type="text" class="form-control" placeholder="..." id="matrix-filter">
    </div>
</div>
-->

<table id="matrix-table" class="table">
    <!-- populated by JavaScript -->
</table>

<ul>
    <li>no operand: Implicit <a href="#addrmod-IMP">(IMP)</a></li>
    <li><span class="matrix-mode-imm">#v</span>: Immediate <a href="#addrmod-IMM">(IMM)</a></li>
    <li><span class="matrix-mode-ind">(a)</span>: Indirect <a href="#addrmod-IND">(IND)</a></li>
    <li><span class="matrix-mode-rel">label</span>: Relative <a href="#addrmod-REL">(REL)</a></li>
    <li><span class="matrix-mode-abs">a</span>: Absolute <a href="#addrmod-ABS">(ABS)</a></li>
    <li><span class="matrix-mode-abx">a,X</span>: Absolute indexed <a href="#addrmod-ABXY">(ABX)</a></li>
    <li><span class="matrix-mode-aby">a,Y</span>: Absolute indexed <a href="#addrmod-ABXY">(ABY)</a></li>
    <li><span class="matrix-mode-zpg">d</span>: Zero Page <a href="#addrmod-ZPG">(ZPG)</a></li>
    <li><span class="matrix-mode-zpx">d,X</span>: Zero Page indexed <a href="#addrmod-ZPXY">(ZPX)</a></li>
    <li><span class="matrix-mode-zpy">d,Y</span>: Zero Page indexed <a href="#addrmod-ZPXY">(ZPY)</a></li>
    <li><span class="matrix-mode-izx">(d,X)</span>: Indexed indirect <a href="#addrmod-IZX">(IZX)</a></li>
    <li><span class="matrix-mode-izy">(d),Y</span>: Indirect indexed <a href="#addrmod-IZY">(IZY)</a></li>
    <li>Left number: Cycle count</li>
    <li>Right number: Size (bytes)</li>
    <li><span class="matrix-stability">!</span>: unstable instruction</li>
    <li><span class="matrix-stability">!!</span>: highly unstable instruction</li>
    <li><span class="matrix-type-generic">Generic operation</span></li>
    <li><span class="matrix-type-read">Read operation</span></li>
    <li><span class="matrix-type-rmw">Read/Modify/Write operation</span></li>
    <li><span class="matrix-type-write">Write operation</span></li>
</ul>

<h2 id="addrmod">Addressing Modes + Cycle timings</h2>

<a href="/static/data/timings.json" download="timings.json" class="btn btn-primary">Download as JSON</a>

<div id="addrmod-container">
    
    Registers:
    <ul>
        <li>
            <b>A</b>, <b>X</b>, <b>Y</b>, <b>S</b>, <b>P</b>:
            registers as <a href="#registers">listed above</a>
        </li>
        <li>
            <b>PC</b> (<a href="#reg-PC">Program Counter</a>): pointer to the current instruction, divided into the following parts:
            <ul>
                <li>PCL: lower byte (8 least significant bit)</li>
                <li>PCH: upper byte (8 most significant bit)</li>
            </ul>
            <code>PC = (PCH << 8) | PCL</code>
        </li>
        <li>
            <b>EA</b> (effective address): used as a temporary 16-bit "register", divided into the following parts:
            <ul>
                <li>EAL: lower byte (8 least significant bit)</li>
                <li>EAH: upper byte (8 most significant bit)</li>
            </ul>
            <code>EA = (EAH << 8) | EAL</code>
        </li>
        <li>
            <b>BA</b> (base address): used as a temporary 16-bit "register", divided into the following parts:
            <ul>
                <li><b>BAL</b>: lower byte (8 least significant bit)</li>
                <li><b>BAH</b>: upper byte (8 most significant bit)</li>
            </ul>
            <code>BA = (BAH << 8) | BAL</code>
        </li>
        <li><b>data</b>: used as a temporary 8-bit "register"</li>
        <li><b>offset</b>: used as a temporary signed 8-bit "register"</li>
        <li><b>opcode</b>: the ID of the instruction</li>
    </ul>

    The table is divided up into 4 columns:
    <ul>
        <li>
            <b>Cycle</b>: The number of the cycle.<br>
            <i>Note</i>: The <code>0th</code> cycle is the <code>1st</code> cycle of the next instruction!
        </li>
        <li>
            <b>Value</b>: The value (address) used for memory read/write operations
            <ul>
                <li>
                    <b>REGISTER++</b>: first use the value of the <b>REGISTER</b>, then increment it afterwards (post-increment)
                </li>
                <li>
                    <b>REGISTER--</b>: first use the value of the <b>REGISTER</b>, then decrement it afterwards (post-decrement)
                </li>
            </ul>
        </li>
        <li>
            <b>Bus</b>: The destination (= register) of a memory read operation. If the bus is <b>WRITE</b>,
            the operation is a memory write instruction.
        </li>
        <li>
            <b>Comment</b> Description of the steps in a cycle:
            <ul>
                <li>
                    <b>Fetch DESTINATION</b>: Read memory at the address declared in the <b>Value</b>
                    column and store it in <b>DESTINATION</b>.<br>
                    (<b>Fetch next</b> = fetch opcode of next instruction)
                </li>
                <li><b>Increment REGISTER</b>: Increment the specified <b>REGISTER</b> (usually the last step in the cycle)</li>
                <li><b>Decrement REGISTER</b>: Decrement the specified <b>REGISTER</b> (usually the last step in the cycle)</li>
                <li><b>Execute</b>: Execute the actual instruction and operate on the data provided</li>
            </ul>
        </li>
    </ul>

    Operation pseudo-code (C-like):
    <ul>
        <li><code>[address]</code>: Read memory at a given <code>address</code></li>
        <li><code>arg</code>: The additional data of an instruction (8-bit or 16-bit, if present)</li>
        <li><code>++S</code>: increment the <a href="#reg-S">stack pointer</a>, then use it (pre-increment)</li>
        <li><code>S--</code>: first use the <a href="#reg-S">stack pointer</a>, then decrement it afterwards (post-decrement)</li>
    </ul>

    If there is additional information for a certain steps, it is marked with one or more <b>asterisks*</b> and explained below the table.

    <!---------------------------------
            IMPLICIT ADDRESSING        
    ---------------------------------->

    <h3 id="addrmod-IMP">
        <a data-bs-toggle="collapse" href="#addrmod-container-IMP" class="btn btn-secondary">
            Implicit (<b>IMP</b>)
        </a>
    </h3>

    <div id="addrmod-container-IMP" class="collapse">
        <i>Instructions:</i>
        <span id="addrmod-instr-imp"></span><br>

        (<a href="#addrmod-BRK">BRK</a>,
        <a href="#addrmod-RTI">RTI</a>,
        <a href="#addrmod-BRK">RTS</a>,
        <a href="#addrmod-PHAP">PHA</a>,
        <a href="#addrmod-PHAP">PHP</a>,
        <a href="#addrmod-PLAP">PHA</a>, and
        <a href="#addrmod-PLAP">PHP</a>
        have an implicit addressing mode, but work differently)<br>

        <i>Example:</i>
        <code>PHP</code> or <code>ASL A</code><br>

        <i>Operation:</i>
        <code>data = A</code> (depending on the operation; not actually stored in the data register)

        <table class="table" id="addrmod-table-imp">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-imp"></div>
    </div>

    <!----------------------------------
            IMMEDIATE ADDRESSING        
    ----------------------------------->

    <h3 id="addrmod-IMM">
        <a data-bs-toggle="collapse" href="#addrmod-container-IMM" class="btn btn-secondary">
            Immediate (<b>IMM</b>)
        </a>
    </h3>

    <div id="addrmod-container-IMM" class="collapse">
        <i>Instructions:</i>
        <span id="addrmod-instr-imm"></span><br>

        <i>Example:</i>
        <code>ORA #$69</code><br>

        <i>Operation:</i>
        <code>data = arg</code> (8-bit)

        <table class="table" id="addrmod-table-imm">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-imm"></div>
    </div>

    <!---------------------------------
            INDIRECT ADDRESSING        
    ---------------------------------->

    <h3 id="addrmod-IND">
        <a data-bs-toggle="collapse" href="#addrmod-container-IND" class="btn btn-secondary">
            Indirect (<b>IND</b>)
        </a>
    </h3>

    <div id="addrmod-container-IND" class="collapse">
        <i>Instructions:</i>
        <span id="addrmod-instr-ind"></span><br>

        <i>Example:</i>
        <code>JMP ($CAFE)</code><br>

        <i>Operation:</i>
        <code>PC = [arg]</code> (16-bit)

        <table class="table" id="addrmod-table-ind">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-ind"></div>
    </div>

    <!---------------------------------
            RELATIVE ADDRESSING        
    ---------------------------------->

    <h3 id="addrmod-REL">
        <a data-bs-toggle="collapse" href="#addrmod-container-REL" class="btn btn-secondary">
            Relative (<b>REL</b>)
        </a>
    </h3>

    <div id="addrmod-container-REL" class="collapse">
        <i>Instructions:</i>
        <span id="addrmod-instr-rel"></span><br>

        <i>Example:</i>
        <code>BEQ label</code> &nbsp; (<code>label</code> is converted into a signed offset by the compiler)<br>

        <i>Operation:</i>
        <code>PC = PC + arg</code> (8-bit, signed)

        <table class="table" id="addrmod-table-rel">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-rel"></div>
    </div>

    <!---------------------------------
            ABSOLUTE ADDRESSING        
    ---------------------------------->

    <h3 id="addrmod-ABS">
        <a data-bs-toggle="collapse" href="#addrmod-container-ABS" class="btn btn-secondary">
            Absolute (<b>ABS</b>)
        </a>
    </h3>

    <div id="addrmod-container-ABS" class="collapse">
        (<a href="#addrmod-JMP">JMP</a> and <a href="#addrmod-JSR">JSR</a>
        have an absolute addressing mode, but work differently)<br>

        <i>Example:</i>
        <code>ADC $4269</code><br>

        <i>Operation:</i>
        <code>data = [arg]</code> (16-bit)

        <!--        READ        -->

        <h4 id="addrmod-ABSR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-absr"></span>

        <table class="table" id="addrmod-table-absr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-absr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-ABSM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-absm"></span>

        <table class="table" id="addrmod-table-absm">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-absm"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-ABSW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-absw"></span>

        <table class="table" id="addrmod-table-absw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-absw"></div>
    </div>

    <!-----------------------------------------
            ABSOLUTE INDEXED ADDRESSING        
    ------------------------------------------>

    <h3 id="addrmod-ABXY">
        <a data-bs-toggle="collapse" href="#addrmod-container-ABXY" class="btn btn-secondary">
            Absolute indexed X/Y (<b>ABX</b>, <b>ABY</b>)
        </a>
    </h3>

    <div id="addrmod-container-ABXY" class="collapse">
        <i>Note:</i>
        <code>I</code> represents either the <a href="#reg-X">X</a> or <a href="#reg-Y">Y</a> register, depending on the mode.<br>

        <i>Example:</i>
        <code>LDX $1234,X</code> or
        <code>EOR $5566,Y</code><br>

        <i>Operation:</i>
        <code>data = [arg + I]</code> (16-bit)

        <!--        READ        -->

        <h4 id="addrmod-ABXYR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-abxyr"></span>

        <table class="table" id="addrmod-table-abxyr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-abxyr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-ABXYM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-abxym"></span><br>

        <i>Note</i>: This mode does not exist for <i>ABY</i>

        <table class="table" id="addrmod-table-abxym">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-abxym"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-ABXYW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-abxyw"></span>

        <table class="table" id="addrmod-table-abxyw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-abxyw"></div>
    </div>

    <!----------------------------------
            ZERO PAGE ADDRESSING        
    ----------------------------------->

    <h3 id="addrmod-ZPG">
        <a data-bs-toggle="collapse" href="#addrmod-container-ZPG" class="btn btn-secondary">
            Zero Page (<b>ZPG</b>)
        </a>
    </h3>

    <div id="addrmod-container-ZPG" class="collapse">
        <i>Example:</i>
        <code>CMP $7F</code><br>

        <i>Operation:</i>
        <code>data = [arg] (8-bit)</code>

        <!--        READ        -->

        <h4 id="addrmod-ZPGR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpgr"></span>

        <table class="table" id="addrmod-table-zpgr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpgr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-ZPGM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpgm"></span>

        <table class="table" id="addrmod-table-zpgm">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpgm"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-ZPGW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpgw"></span>

        <table class="table" id="addrmod-table-zpgw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpgw"></div>
    </div>

    <!------------------------------------------
            ZERO PAGE INDEXED ADDRESSING        
    ------------------------------------------->

    <h3 id="addrmod-ZPXY">
        <a data-bs-toggle="collapse" href="#addrmod-container-ZPXY" class="btn btn-secondary">
            Zero Page indexed X/Y (<b>ZPX</b>, <b>ZPY</b>)
        </a>
    </h3>

    <div id="addrmod-container-ZPXY" class="collapse">
        <i>Note:</i>
        <code>I</code> represents either the <a href="#reg-X">X</a> or <a href="#reg-Y">Y</a> register, depending on the mode.<br>

        <i>Example:</i>
        <code>STY $EE,X</code> or
        <code>LAX $DD,Y</code><br>

        <i>Operation:</i>
        <code>data = [(arg + I) & $FF]</code> (8-bit)

        <!--        READ        -->

        <h4 id="addrmod-ZPXYR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpxyr"></span>

        <table class="table" id="addrmod-table-zpxyr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpxyr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-ZPXYM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpxym"></span><br>

        <i>Note</i>: This mode does not exist for <i>ABY</i>

        <table class="table" id="addrmod-table-zpxym">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpxym"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-ZPXYW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-zpxyw"></span>

        <table class="table" id="addrmod-table-zpxyw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-zpxyw"></div>
    </div>

    <!-----------------------------------------
            INDEXED INDIRECT ADDRESSING        
    ------------------------------------------>

    <h3 id="addrmod-IZX">
        <a data-bs-toggle="collapse" href="#addrmod-container-IZX" class="btn btn-secondary">
            Indexed Indirect X (<b>IZX</b>)
        </a>
    </h3>

    <div id="addrmod-container-IZX" class="collapse">
        <i>Example:</i>
        <code>LDA ($AB,X)</code><br>

        <i>Operation:</i>
        <code>data = [[(arg + X) & $FF] + ([(arg + X + 1) & $FF] << 8)]</code> (8-bit)

        <!--        READ        -->

        <h4 id="addrmod-IZXR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izxr"></span>

        <table class="table" id="addrmod-table-izxr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izxr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-IZXM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izxm"></span>

        <table class="table" id="addrmod-table-izxm">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izxm"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-IZXW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izxw"></span>

        <table class="table" id="addrmod-table-izxw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izxw"></div>
    </div>

    <!-----------------------------------------
            INDIRECT INDEXED ADDRESSING        
    ------------------------------------------>

    <h3 id="addrmod-IZY">
        <a data-bs-toggle="collapse" href="#addrmod-container-IZY" class="btn btn-secondary">
            Indirect Indexed Y (<b>IZY</b>)
        </a>
    </h3>

    <div id="addrmod-container-IZY" class="collapse">
        <i>Example:</i>
        <code>SBC ($EF),X</code><br>

        <i>Operation:</i>
        <code>data = [[arg] + ([(arg + 1) & $FF] << 8) + Y]</code> (8-bit)

        <!--        READ        -->

        <h4 id="addrmod-IZYR">Read</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izyr"></span>

        <table class="table" id="addrmod-table-izyr">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izyr"></div>

        <!--        READ/MODIFY/WRITE        -->

        <h4 id="addrmod-IZYMM">Read-Modify-Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izym"></span>

        <table class="table" id="addrmod-table-izym">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izym"></div>

        <!--        WRITE        -->

        <h4 id="addrmod-IZYW">Write</h4>

        <i>Instructions:</i>
        <span id="addrmod-instr-izyw"></span>

        <table class="table" id="addrmod-table-izyw">
            <!-- populated by JavaScript -->
        </table>

        <div id="addrmod-notes-izyw"></div>
    </div>

    <!---------------------------
            UNCATEGORIZED        
    ---------------------------->

    <h3>Uncategorized</h3>

    These instructions don't follow the rules of the addressing modes above,
    despite some of them being labeled as such (e.g. <code>JMP abs</code>)

    <!--        BRK        -->

    <h4 id="addrmod-BRK">
        <a data-bs-toggle="collapse" href="#addrmod-container-BRK" class="btn btn-secondary">
            BRK
        </a>
    </h4>

    <div id="addrmod-container-BRK" class="collapse">
        <i>Operation:</i>
        <code>[S--] = PCH; [S--] = PCL; [S--] = P; PC = [$FFFE] | ([$FFFF] << 8)</code><br>
        (see <a href="#flag-B">B flag</a> for more information)

        <table class="table" id="addrmod-table-brk">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        RTI        -->

    <h4 id="addrmod-RTI">
        <a data-bs-toggle="collapse" href="#addrmod-container-RTI" class="btn btn-secondary">
            RTI
        </a>
    </h4>

    <div id="addrmod-container-RTI" class="collapse">
        <i>Operation:</i>
        <code>P = [++S]; PC = ([++S] << 8) | [++S]</code><br>
        (see <a href="#flag-I">I flag</a> for more information)

        <table class="table" id="addrmod-table-rti">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        RTS        -->

    <h4 id="addrmod-RTS">
        <a data-bs-toggle="collapse" href="#addrmod-container-RTS" class="btn btn-secondary">
            RTS
        </a>
    </h4>

    <div id="addrmod-container-RTS" class="collapse">
        <i>Operation:</i>
        <code>PC = ([++S] << 8) | [++S]</code>

        <table class="table" id="addrmod-table-rts">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        PHA, PHP        -->

    <h4 id="addrmod-PHAP">
        <a data-bs-toggle="collapse" href="#addrmod-container-PHAP" class="btn btn-secondary">
            PHA, PHP
        </a>
    </h4>

    <div id="addrmod-container-PHAP" class="collapse">
        <i>Operation:</i>
        <code>[S--] = A/P</code><br>
        (see <a href="#flag-B">B flag</a> for more information)

        <table class="table" id="addrmod-table-phap">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        PLA, PLP        -->

    <h4 id="addrmod-PLAP">
        <a data-bs-toggle="collapse" href="#addrmod-container-PLAP" class="btn btn-secondary">
            PLA, PLP
        </a>
    </h4>

    <div id="addrmod-container-PLAP" class="collapse">
        <i>Operation:</i>
        <code>A/P = [++S]</code>

        <table class="table" id="addrmod-table-plap">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        JSR        -->

    <h4 id="addrmod-JSR">
        <a data-bs-toggle="collapse" href="#addrmod-container-JSR" class="btn btn-secondary">
            JSR
        </a>
    </h4>

    <div id="addrmod-container-JSR" class="collapse">
        <i>Example:</i>
        <code>JSR $BEEF</code><br>

        <i>Operation:</i>
        <code>[S--] = PCH; [S--] = PCL; PC = arg</code> (16-bit)

        <table class="table" id="addrmod-table-jsr">
            <!-- populated by JavaScript -->
        </table>
    </div>

    <!--        JMP        -->

    <h4 id="addrmod-JMP">
        <a data-bs-toggle="collapse" href="#addrmod-container-JMP" class="btn btn-secondary">
            JMP
        </a>
    </h4>

    <div id="addrmod-container-JMP" class="collapse">
        <i>Example:</i>
        <code>JMP $BABE</code><br>

        <i>Operation:</i>
        <code>PC = arg</code> (16-bit)

        <table class="table" id="addrmod-table-jmp">
            <!-- populated by JavaScript -->
        </table>
    </div>

</div>

<hr>

<h2 id="instr">Instructions</h2>

Operation pseudo-code (C-like):
<ul>
    <li><code>[address]</code>: Read memory at a given <code>address</code></li>
    <li><code>arg</code>: The additional data of an instruction (8-bit or 16-bit, if present)</li>
    <li><code>++S</code>: increment the <a href="#reg-S">stack pointer</a>, then use it (pre-increment)</li>
    <li><code>S--</code>: first use the <a href="#reg-S">stack pointer</a>, then decrement it afterwards (post-decrement)</li>
    <li><code>#</code>: the fetched value (depending on the <a href="#addrmod">Addressing mode</a>)</li>
</ul>

<table class="table" id="instr-official">
    <thead>
        <tr>
            <th>Mnemonic</th>
            <th>Operation</th>
            <th>NV-BDIZC</th>
        </tr>
    </thead>
    <tbody>
        <!-- populated by JavaScript -->
    </tbody>
</table>

<hr>

<h2 id="unoff">Unofficial Instructions</h2>

<table class="table" id="instr-unofficial">
    <thead>
        <tr>
            <th>Mnemonic</th>
            <th>Operation</th>
            <th>NV-BDIZC</th>
            <th>Stability</th>
        </tr>
    </thead>
    <tbody>
        <!-- populated by JavaScript -->
    </tbody>
</table>

Some of the illegal opcodes are just combinations of two operations:
<ul>
    <li><code>SLO := ASL + ORA</code></li>
    <li><code>RLA := ROL + AND</code></li>
    <li><code>SRE := LSR + EOR</code></li>
    <li><code>RRA := ROR + ADC</code></li>
    <li><code>LAX := LDA + LDX</code></li>
    <li><code>DCP := DEC + CMP</code></li>
    <li><code>ISC := INC + SBC</code></li>
    <li><code>ANC := AND + ASL</code></li>
    <li><code>ANC := AND + ROL</code></li>
    <li><code>ALR := AND + LSR</code></li>
    <li><code>ARR := AND + ROR</code></li>
    <li><code>XAA := TXA + AND</code></li>
    <li><code>LAX := LDA + TAX</code></li>
    <li><code>SBC := SBC + NOP</code></li>
</ul>

Quirks with other operations:
<ul>
    <li>
        The <a href="#SAX-unoff">SAX</a> operation (<code>A&X</code>) is
        performed by putting the <a href="#reg-A">A</a> and <a href="#reg-A">X</a> register onto the bus at the same time.<br>
    </li>

    <li>
        The <a href="#ANC-unoff">ANC</a> operation (<code>A&#</code>) technically
        performs an <a href="#AND">AND</a> operation, but puts bit 7 into the <a href="#flag-C">carry</a>
        (like an <a href="#ASL">ASL</a>/<a href="#ROL">ROL</a> operation would).<br>
    </li>

    <li>
        The <a href="#ARR-unoff">ANC</a> operation (<code>(A&#)>>1</code>) perform a step
        between the <a href="#AND">AND</a> and the <a href="#ROR">ROR</a>: <a href="#flag-V">V</a> is set according to
        <code>(A&#)+#</code>; bit 0 does <i>not</i> go into the <a href="#flag-C">carry</a>, but
        bit 7 is exchanged with the <a href="#flag-C">carry</a>.<br>
    </li>

    <li>
        The <a href="#AXS-unoff">AXS</a> operation (<code>A&X-#</code>) performs
        a <a href="#CMP">CMP</a> and <a href="#DEX">DEX</a> at the same time.
        The minus therefore sets the flags like a <a href="#CMP">CMP</a> (not <a href="#SBC">SBC</a>) would<br>
    </li>

    <li>
        The <a href="#KIL-unoff">KIL</a> operation halts the CPU. In order to work again, it has to be reset.<br>
    </li>

    <li>
        The <a href="#AHX-unoff">AHX</a>, <a href="#SHX-unoff">SHX</a> and <a href="#SHY-unoff">SHY</a> operations
        all contain an <code>&H</code> operation, with <code>H</code> begin the <i>high byte of the provided address + 1</i>.
        Sometimes this part of the operation is not executed, therefore they are considered unstable.
        Also page boundary crossing does not work as expected.<br>
    </li>

    <li>
        The various <a href="#NOP-unoff">NOPs</a> all fetch the data according to their addressing mode, but have no effect otherwise.<br>
    </li>

    <li>
        Both the <a href="#XAA-unoff">XAA</a> and the <a href="#LAX-unoff">LAX (immediate)</a> instruction are
        considered highly unstable. This is because they operate with a magic constant (which depends on CPU model
        and possibly other factors; typically <code>$00</code>, <code>$FF</code>, <code>$EE</code> etc.). This
        adds a <code>(A|magic)&</code> term to the equation and effectively results in the following operations:
        <ul>
            <li><a href="#XAA-unoff">XAA</a>: <code>A=(A|magic)&X&#</code></li>
            <li><a href="#LAX-unoff">LAX</a>: <code>A=X=(A|magic)&#</code></li>
        </ul>
    </li>
</ul>

<hr>

<h2 id="int">Interrupts</h2>

Work in progress!

<hr>

<h2 id="src">Sources</h2>

These are the sources I used to gather all this information:
<ul>
    <li><a target="_blank" href="https://wiki.nesdev.org/w/index.php?title=CPU_addressing_modes">https://wiki.nesdev.org/w/index.php?title=CPU_addressing_modes</a>
    <li><a target="_blank" href="https://www.nesdev.com/6502_cpu.txt">https://www.nesdev.com/6502_cpu.txt</a>
    <li><a target="_blank" href="http://www.oxyron.de/html/opcodes02.html">http://www.oxyron.de/html/opcodes02.html</a>
    <li><a target="_blank" href="https://www.masswerk.at/nowgobang/2021/6502-illegal-opcodes">https://www.masswerk.at/nowgobang/2021/6502-illegal-opcodes</a>
    <li><a target="_blank" href="https://wiki.nesdev.org/w/index.php?title=CPU_unofficial_opcodes">https://wiki.nesdev.org/w/index.php?title=CPU_unofficial_opcodes</a>
</ul>

<?php require_once 'private/footer.php'; ?>