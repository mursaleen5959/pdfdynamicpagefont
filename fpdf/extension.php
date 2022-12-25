<?php
class PDF extends FPDF
{
    protected $date;
    protected $squareHeight;
    protected $squareWidth;
    protected $longestMonth;
    protected $tinySquareSize;

    function __construct($orientation = "L", $format = "A4")
    {
        parent::__construct($orientation, "mm", $format);
        // compute longest month name
        $this->longestMonth = "";
        for ($month = 1; $month <= 12; ++$month)
        {
            $monthYear = gmdate("F Y", jdtounix($this->MDYtoJD($month, 1, 2009)));
            if (strlen($monthYear) > strlen($this->longestMonth))
            {
                $this->longestMonth = $monthYear;
            }
        }
        // compute font size
        $this->tinySquareSize = 4;
        $this->headerFontSize = 30;
        $this->SetFont("Times", "B", $this->headerFontSize);
        $width = $this->w - $this->lMargin - $this->rMargin;
        while ($this->GetStringWidth($this->longestMonth) > $width - $this->tinySquareSize * 22)
        {
            --$this->headerFontSize;
            $this->SetFont("Times", "B", $this->headerFontSize);
        }
    }

    // useful date manipulation routines

    function JDtoYMD($date, &$year, &$month, &$day)
    {
        $string = JDToGregorian($date);
        $month = strtok($string, " -/");
        $day = strtok(" -/");
        $year = strtok(" -/");
    }

    function MDYtoJD($month, $day, $year)
    {
        if (!$month || !$day || !$year)
            return 0;
        $a = floor((14 - $month) / 12);
        $y = floor($year + 4800 - $a);
        $m = floor($month + 12 * $a - 3);
        $jd = $day + floor((153 * $m + 2) / 5) + $y * 365;
        $jd += floor($y / 4) - floor($y / 100) + floor($y / 400) - 32045;
        return $jd;
    }

    function lastMonth($date)
    {
        $this->JDtoYMD($date, $year, $month, $day);
        if (--$month == 0) {
            $month = 12;
            $year--;
        }
        return GregorianToJD($month, $day, $year);
    }

    function nextMonth($date)
    {
        $this->JDtoYMD($date, $year, $month, $day);
        if (++$month > 12) {
            $month = 1;
            ++$year;
        }
        return GregorianToJD($month, $day, $year);
    }

    function isWeekHoliday($date, $dayOfWeek, $weekOfMonth, $monthOfDate)
    {
        $this->JDtoYMD($date, $year, $month, $day);
        if ($monthOfDate != $month)
            return 0;
        $jd = jdtounix($date);
        $dow = gmdate("w", $jd);
        if ($dow != $dayOfWeek)
            return 0;
        $daysInMonth = gmdate("t", $jd);
        if ($weekOfMonth > 5 && $day + 6 > $daysInMonth)
            return 1;
        if ($day > ($weekOfMonth - 1) * 7 && $day <= ($weekOfMonth * 7))
            return 1;
        return 0;
    }

    function printHoliday($date)
    {
        $x = $this->x;
        $y = $this->y;
        $height = 5.5;
        if ($this->squareHeight < 50)
            $height = 4;
        $widthPercent = .92;
        $fontSize = 11;
        //$holiday = $this->isHoliday($date);
        $holiday = '';
        if (strlen($holiday))
        {
            $wd = gmdate("w", jdtounix($date));
            if ($wd != 0 && $wd != 6)
                $this->Cell($this->squareWidth, $this->squareHeight, "", 0, 0, "", true);
            $this->SetXY($x + $this->squareWidth * (1 - $widthPercent) / 2, $y + $this->squareHeight * 0.83);
            $this->SetFont("Helvetica", "B", $fontSize);
            $this->Cell($this->squareWidth * $widthPercent, $height, $holiday, 0, 0, "C");
        }
    }

    function printMonth($date,$value_to_fill,$record_date)
    {
        $this->date = $date;
        $this->JDtoYMD($date, $year, $month, $day);
        $this->AddPage();
        // compute size base on current settings
        $width = $this->w - $this->lMargin - $this->rMargin;
        $height = $this->h - $this->tMargin - $this->bMargin;
        // print prev and next calendars
        $this->setXY($this->lMargin, $this->tMargin);
        //    $this->tinyCalendar($this->lastMonth($date), $this->tinySquareSize);
        $this->setXY($this->lMargin + $width - $this->tinySquareSize * 7, $this->tMargin);
        //    $this->tinyCalendar($this->nextMonth($date), $this->tinySquareSize);
        // print header
        $firstLine = $this->tinySquareSize * 8 + $this->tMargin;
        $monthStr = strtoupper(gmdate("F Y", jdtounix($date)));
        $this->SetXY($this->lMargin, $this->tMargin);
        $this->SetFont("Times", "B", $this->headerFontSize);
        $this->Cell($width, $firstLine, $monthStr, 0, 0, "C");
        // compute number of weeks in month.
        $wd = gmdate("w", jdtounix($date));
        $start = $date - $wd;
        $numDays = $this->nextMonth($date) - $start;
        $numWeeks = 0;
        while ($numDays > 0)
        {
            $numDays -= 7;
            ++$numWeeks;
        }
        // compute horizontal lines
        $this->squareHeight = ($height - 6 - $firstLine) / $numWeeks;
        $horizontalLines = array($firstLine, 6);
        for ($i = 0; $i < $numWeeks; ++$i)
        {
            $horizontalLines[$i + 2] = $this->squareHeight;
        }
        // compute vertical lines
        $this->squareWidth = $width / 7;
        $verticalLines = array($this->lMargin, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth, $this->squareWidth);
        // print days of week
        $x = 0;
        $this->SetFont("Times", "B", 12);
        for ($i = 1; $i <= 7; ++$i)
        {
            $x += $verticalLines[$i - 1];
            $this->SetXY($x, $firstLine);
            $day = gmdate("l", jdtounix($this->MDYtoJD(2, $i, 2009)));
            $this->Cell($verticalLines[$i], 6, $day, 0, 0, "C");
        }
        // print numbers in boxes
        $wd = gmdate("w", jdtounix($date));
        $cur = $date - $wd;
        $this->SetFont("Times", "B", 20);
        $x = 0;
        $y = $horizontalLines[0];
        for ($k = 0; $k < $numWeeks; $k++)
        {
            $y += $horizontalLines[$k + 1];
            for ($i = 0; $i < 7; $i++)
            {
                $this->JDtoYMD($cur, $curYear, $curMonth, $curDay);
                $x += $verticalLines[$i];
                $this->squareWidth = $verticalLines[$i + 1];
                if ($curMonth == $month) {
                    $this->setXY($x, $y);
                    $this->printHoliday($cur);
                    $this->setXY($x, $y);
                    $this->printDay($cur,$value_to_fill,$record_date);
                    $this->SetFont("Times", "B", 20);
                    $this->SetXY($x, $y + 1);
                    $this->Cell(5, 5, $curDay);
                }
                ++$cur;
            }
            $x = 0;
        }
        // print horizontal lines
        $ly = 0;
        $ry = 0;
        foreach ($horizontalLines as $key => $value)
        {
            $ly += $value;
            $ry += $value;
            $this->Line($this->lMargin, $ly, $this->lMargin + $width, $ry);
        }
        // print vertical lines
        $lx = 0;
        $rx = 0;
        foreach ($verticalLines as $key => $value)
        {
            $lx += $value;
            $rx += $value;
            $this->Line($lx, $firstLine, $rx, $firstLine + 6 + $this->squareHeight * $numWeeks);
        }
    }
    function printDay($date,$value_to_fill,$record_day='')
    {
        // add logic here to customize a day
        $this->JDtoYMD($date, $year, $month, $day);
        // if ($month == 1 && $day == 10) {
        foreach ($value_to_fill as $key => $value) {
            if ( $day == $key )
            {
                $this->SetXY($this->x, $this->y + $this->squareHeight / 2);
                $this->SetFont("Arial", "B", 14);
                // Define the Custom color here
                $this-> SetDrawColor(255,0,0);
//                $this-> SetFillColor(255,0,0);
//                $this-> SetTextColor(255,0,0);
                $this->Cell($this->squareWidth, 5, $value, 0, 0, "C");
//                $this-> SetTextColor(0,0,0);
            }
        }
    }

    function WordWrap(&$text, $maxwidth)
    {
        $text = trim($text);
        if ($text === '')
            return 0;
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($wordwidth > $maxwidth) {
                    // Word is too long, we cut it
                    for ($i = 0; $i < strlen($word); $i++) {
                        $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                        if ($width + $wordwidth <= $maxwidth) {
                            $width += $wordwidth;
                            $text .= substr($word, $i, 1);
                        } else {
                            $width = $wordwidth;
                            $text = rtrim($text) . "\n" . substr($word, $i, 1);
                            $count++;
                        }
                    }
                } elseif ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word . ' ';
                } else {
                    $width = $wordwidth + $space;
                    $text = rtrim($text) . "\n" . $word . ' ';
                    $count++;
                }
            }
            $text = rtrim($text) . "\n";
            $count++;
        }
        $text = rtrim($text);
        return $count;
    }
    function subWrite($h, $txt, $link = '', $subFontSize = 12, $subOffset = 0)
    {
        // resize font
        $subFontSizeold = $this->FontSizePt;
        $this->SetFontSize($subFontSize);

        // reposition y
        $subOffset = ((($subFontSize - $subFontSizeold) / $this->k) * 0.3) + ($subOffset / $this->k);
        $subX        = $this->x;
        $subY        = $this->y;
        $this->SetXY($subX, $subY - $subOffset);

        //Output text
        $this->Write($h, $txt, $link);

        // restore y position
        $subX        = $this->x;
        $subY        = $this->y;
        $this->SetXY($subX,  $subY + $subOffset);

        // restore font size
        $this->SetFontSize($subFontSizeold);
    }
}