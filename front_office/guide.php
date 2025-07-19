<?php
// guide.php
// Ensure no session or database connection is required for standalone access
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide des Tailles - GOAT'S</title>
    <style>
        /* Embedded styles for standalone access; modal will inherit produit.php styles */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
            padding: 20px;
            background-color: #fff;
        }
        .modal-content {
            font-size: 14px;
            border-radius: 8px;
            padding: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-top: 15px;
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 13px;
        }
        th {
            background-color: black;
            color: white;
        }
        .note {
            font-size: 12px;
            color: #777;
            font-style: italic;
        }
        h2 {
            font-size: 24px;
            font-weight: 600;
            color: black;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="modal-content">
        <h2>Guide des Tailles</h2>

        <!-- Guide des Tailles Femme -->
        <div class="section-title">GUIDE DES TAILLES FEMME</div>

        <h5>VÊTEMENT</h5>
        <table>
            <tr>
                <th>Taille</th>
                <th>34</th>
                <th>36</th>
                <th>38</th>
                <th>40</th>
                <th>42</th>
                <th>44</th>
                <th>46</th>
                <th>48</th>
                <th>50</th>
            </tr>
            <tr>
                <td>Poitrine (cm)</td>
                <td>82</td>
                <td>86</td>
                <td>90</td>
                <td>94</td>
                <td>98</td>
                <td>102</td>
                <td>106</td>
                <td>110</td>
                <td>114</td>
            </tr>
            <tr>
                <td>Taille (cm)</td>
                <td>64</td>
                <td>66</td>
                <td>70</td>
                <td>74</td>
                <td>78</td>
                <td>82</td>
                <td>86</td>
                <td>90</td>
                <td>94</td>
            </tr>
            <tr>
                <td>Hanches (cm)</td>
                <td>90</td>
                <td>94</td>
                <td>98</td>
                <td>102</td>
                <td>106</td>
                <td>110</td>
                <td>114</td>
                <td>118</td>
                <td>122</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Taille</th>
                <th>XS</th>
                <th>S</th>
                <th>M</th>
                <th>L</th>
                <th>XL</th>
                <th>XXL</th>
                <th>XXXL</th>
                <th>XXXXL</th>
            </tr>
            <tr>
                <td>Poitrine (cm)</td>
                <td>82</td>
                <td>86</td>
                <td>90</td>
                <td>96</td>
                <td>102</td>
                <td>108</td>
                <td>114</td>
                <td>120</td>
            </tr>
            <tr>
                <td>Taille (cm)</td>
                <td>62</td>
                <td>66</td>
                <td>70</td>
                <td>76</td>
                <td>82</td>
                <td>88</td>
                <td>94</td>
                <td>100</td>
            </tr>
            <tr>
                <td>Hanches (cm)</td>
                <td>90</td>
                <td>94</td>
                <td>98</td>
                <td>104</td>
                <td>110</td>
                <td>116</td>
                <td>122</td>
                <td>128</td>
            </tr>
        </table>
        <p class="note">• Les tailles indiquées dans le guide des tailles se réfèrent aux mensurations du corps et non aux tailles des vêtements</p>

        <h5>CHAUSSURES</h5>
        <table>
            <tr>
                <th>Taille</th>
                <th>36</th>
                <th>37</th>
                <th>38</th>
                <th>39</th>
                <th>40</th>
                <th>41</th>
            </tr>
            <tr>
                <td>Longueur pied (cm)</td>
                <td>23.4</td>
                <td>24.0</td>
                <td>24.6</td>
                <td>25.3</td>
                <td>26</td>
                <td>26.6</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Taille (épaisseur)</th>
                <th>36/37</th>
                <th>38/39</th>
                <th>40/41</th>
            </tr>
            <tr>
                <td>Longueur pied (cm)</td>
                <td>24</td>
                <td>25.3</td>
                <td>26.6</td>
            </tr>
        </table>
        <p class="note">• Les tailles indiquées dans le guide des tailles se réfèrent à la pointure et non à la dimension de la chaussure.</p>

        <!-- Guide des Tailles Homme -->
        <div class="section-title">GUIDE DES TAILLES HOMME</div>

        <h5>VÊTEMENT</h5>
        <table>
            <tr>
                <th>Taille</th>
                <th>36</th>
                <th>38</th>
                <th>40</th>
                <th>42</th>
                <th>44</th>
                <th>46</th>
                <th>48</th>
                <th>50</th>
                <th>52</th>
            </tr>
            <tr>
                <td>Poitrine (cm)</td>
                <td>88</td>
                <td>92</td>
                <td>96</td>
                <td>100</td>
                <td>104</td>
                <td>108</td>
                <td>112</td>
                <td>116</td>
                <td>120</td>
            </tr>
            <tr>
                <td>Taille (cm)</td>
                <td>76</td>
                <td>80</td>
                <td>84</td>
                <td>88</td>
                <td>92</td>
                <td>96</td>
                <td>100</td>
                <td>104</td>
                <td>108</td>
            </tr>
            <tr>
                <td>Hanches (cm)</td>
                <td>92</td>
                <td>96</td>
                <td>100</td>
                <td>104</td>
                <td>108</td>
                <td>112</td>
                <td>116</td>
                <td>120</td>
                <td>124</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Taille</th>
                <th>XS</th>
                <th>S</th>
                <th>M</th>
                <th>L</th>
                <th>XL</th>
                <th>XXL</th>
                <th>XXXL</th>
                <th>XXXXL</th>
            </tr>
            <tr>
                <td>Poitrine (cm)</td>
                <td>88</td>
                <td>92</td>
                <td>96</td>
                <td>100</td>
                <td>104</td>
                <td>110</td>
                <td>116</td>
                <td>122</td>
            </tr>
            <tr>
                <td>Taille (cm)</td>
                <td>76</td>
                <td>80</td>
                <td>84</td>
                <td>88</td>
                <td>92</td>
                <td>98</td>
                <td>104</td>
                <td>110</td>
            </tr>
            <tr>
                <td>Hanches (cm)</td>
                <td>92</td>
                <td>96</td>
                <td>100</td>
                <td>104</td>
                <td>108</td>
                <td>114</td>
                <td>120</td>
                <td>126</td>
            </tr>
        </table>
        <p class="note">• Les tailles indiquées dans le guide des tailles se réfèrent aux mensurations du corps et non aux tailles des vêtements</p>

        <h5>CHAUSSURES</h5>
        <table>
            <tr>
                <th>Taille</th>
                <th>39</th>
                <th>40</th>
                <th>41</th>
                <th>42</th>
                <th>43</th>
                <th>44</th>
                <th>45</th>
                <th>46</th>
            </tr>
            <tr>
                <td>Longueur pied (cm)</td>
                <td>25.3</td>
                <td>26</td>
                <td>26.6</td>
                <td>27.3</td>
                <td>28</td>
                <td>28.6</td>
                <td>29.3</td>
                <td>30</td>
            </tr>
        </table>

        <table>
            <tr>
                <th>Taille (épaisseur)</th>
                <th>39/40</th>
                <th>41/42</th>
                <th>43/44</th>
                <th>45/46</th>
            </tr>
            <tr>
                <td>Longueur pied (cm)</td>
                <td>25.3</td>
                <td>26.6</td>
                <td>28</td>
                <td>29.3</td>
            </tr>
        </table>
        <p class="note">• Les tailles indiquées dans le guide des tailles se réfèrent à la pointure et non à la dimension de la chaussure.</p>
    </div>
</body>
</html>