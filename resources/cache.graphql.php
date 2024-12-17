<?php
return array (
  'loc' => 
  array (
    'start' => 0,
    'end' => 6963,
  ),
  'kind' => 'Document',
  'definitions' => 
  array (
    0 => 
    array (
      'loc' => 
      array (
        'start' => 0,
        'end' => 5767,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 131,
          'end' => 136,
        ),
        'kind' => 'Name',
        'value' => 'Query',
      ),
      'interfaces' => 
      array (
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 141,
            'end' => 362,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 141,
              'end' => 148,
            ),
            'kind' => 'Name',
            'value' => 'authors',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 154,
                'end' => 164,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 154,
                  'end' => 159,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 161,
                  'end' => 164,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 161,
                    'end' => 164,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 169,
                'end' => 182,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 169,
                  'end' => 174,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 176,
                  'end' => 182,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 176,
                    'end' => 182,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 188,
                'end' => 333,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 313,
                  'end' => 318,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 320,
                  'end' => 333,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 320,
                    'end' => 333,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 188,
                  'end' => 308,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 338,
                'end' => 349,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 338,
                  'end' => 344,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 346,
                  'end' => 349,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 346,
                    'end' => 349,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 355,
              'end' => 362,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 356,
                'end' => 361,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 356,
                  'end' => 361,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 365,
            'end' => 545,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 365,
              'end' => 371,
            ),
            'kind' => 'Name',
            'value' => 'author',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 377,
                'end' => 383,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 377,
                  'end' => 379,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 381,
                  'end' => 383,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 381,
                    'end' => 383,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 389,
                'end' => 534,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 514,
                  'end' => 519,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 521,
                  'end' => 534,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 521,
                    'end' => 534,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 389,
                  'end' => 509,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 540,
              'end' => 545,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 540,
                'end' => 545,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 548,
            'end' => 771,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 548,
              'end' => 553,
            ),
            'kind' => 'Name',
            'value' => 'books',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 559,
                'end' => 569,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 559,
                  'end' => 564,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 566,
                  'end' => 569,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 566,
                    'end' => 569,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 574,
                'end' => 587,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 574,
                  'end' => 579,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 581,
                  'end' => 587,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 581,
                    'end' => 587,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 593,
                'end' => 738,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 718,
                  'end' => 723,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 725,
                  'end' => 738,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 725,
                    'end' => 738,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 593,
                  'end' => 713,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 743,
                'end' => 754,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 743,
                  'end' => 749,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 751,
                  'end' => 754,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 751,
                    'end' => 754,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 760,
              'end' => 771,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 761,
                'end' => 770,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 761,
                  'end' => 770,
                ),
                'kind' => 'Name',
                'value' => 'EntryBook',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 774,
            'end' => 956,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 774,
              'end' => 778,
            ),
            'kind' => 'Name',
            'value' => 'book',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 784,
                'end' => 790,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 784,
                  'end' => 786,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 788,
                  'end' => 790,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 788,
                    'end' => 790,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 796,
                'end' => 941,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 921,
                  'end' => 926,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 928,
                  'end' => 941,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 928,
                    'end' => 941,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 796,
                  'end' => 916,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 947,
              'end' => 956,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 947,
                'end' => 956,
              ),
              'kind' => 'Name',
              'value' => 'EntryBook',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 959,
            'end' => 1186,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 959,
              'end' => 972,
            ),
            'kind' => 'Name',
            'value' => 'customColumns',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 978,
                'end' => 988,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 978,
                  'end' => 983,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 985,
                  'end' => 988,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 985,
                    'end' => 988,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 993,
                'end' => 1006,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 993,
                  'end' => 998,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1000,
                  'end' => 1006,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1000,
                    'end' => 1006,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 1012,
                'end' => 1157,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1137,
                  'end' => 1142,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1144,
                  'end' => 1157,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1144,
                    'end' => 1157,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1012,
                  'end' => 1132,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1162,
                'end' => 1173,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1162,
                  'end' => 1168,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1170,
                  'end' => 1173,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1170,
                    'end' => 1173,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1179,
              'end' => 1186,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1180,
                'end' => 1185,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1180,
                  'end' => 1185,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 1189,
            'end' => 1375,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1189,
              'end' => 1201,
            ),
            'kind' => 'Name',
            'value' => 'customColumn',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1207,
                'end' => 1213,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1207,
                  'end' => 1209,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1211,
                  'end' => 1213,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1211,
                    'end' => 1213,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1219,
                'end' => 1364,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1344,
                  'end' => 1349,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1351,
                  'end' => 1364,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1351,
                    'end' => 1364,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1219,
                  'end' => 1339,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1370,
              'end' => 1375,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1370,
                'end' => 1375,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 1378,
            'end' => 1562,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1378,
              'end' => 1383,
            ),
            'kind' => 'Name',
            'value' => 'datas',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1389,
                'end' => 1399,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1389,
                  'end' => 1395,
                ),
                'kind' => 'Name',
                'value' => 'bookId',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1397,
                  'end' => 1399,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1397,
                    'end' => 1399,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1405,
                'end' => 1550,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1530,
                  'end' => 1535,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1537,
                  'end' => 1550,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1537,
                    'end' => 1550,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1405,
                  'end' => 1525,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1556,
              'end' => 1562,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1557,
                'end' => 1561,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1557,
                  'end' => 1561,
                ),
                'kind' => 'Name',
                'value' => 'Data',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 1565,
            'end' => 1742,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1565,
              'end' => 1569,
            ),
            'kind' => 'Name',
            'value' => 'data',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1575,
                'end' => 1581,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1575,
                  'end' => 1577,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1579,
                  'end' => 1581,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1579,
                    'end' => 1581,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1587,
                'end' => 1732,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1712,
                  'end' => 1717,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1719,
                  'end' => 1732,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1719,
                    'end' => 1732,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1587,
                  'end' => 1707,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1738,
              'end' => 1742,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1738,
                'end' => 1742,
              ),
              'kind' => 'Name',
              'value' => 'Data',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        8 => 
        array (
          'loc' => 
          array (
            'start' => 1745,
            'end' => 1964,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1745,
              'end' => 1750,
            ),
            'kind' => 'Name',
            'value' => 'feeds',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1756,
                'end' => 1766,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1756,
                  'end' => 1761,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1763,
                  'end' => 1766,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1763,
                    'end' => 1766,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1771,
                'end' => 1784,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1771,
                  'end' => 1776,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1778,
                  'end' => 1784,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1778,
                    'end' => 1784,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 1790,
                'end' => 1935,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1915,
                  'end' => 1920,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1922,
                  'end' => 1935,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1922,
                    'end' => 1935,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1790,
                  'end' => 1910,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1940,
                'end' => 1951,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1940,
                  'end' => 1946,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1948,
                  'end' => 1951,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1948,
                    'end' => 1951,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1957,
              'end' => 1964,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1958,
                'end' => 1963,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1958,
                  'end' => 1963,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        9 => 
        array (
          'loc' => 
          array (
            'start' => 1967,
            'end' => 2145,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1967,
              'end' => 1971,
            ),
            'kind' => 'Name',
            'value' => 'feed',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 1977,
                'end' => 1983,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1977,
                  'end' => 1979,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1981,
                  'end' => 1983,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1981,
                    'end' => 1983,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 1989,
                'end' => 2134,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2114,
                  'end' => 2119,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2121,
                  'end' => 2134,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2121,
                    'end' => 2134,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 1989,
                  'end' => 2109,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2140,
              'end' => 2145,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2140,
                'end' => 2145,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        10 => 
        array (
          'loc' => 
          array (
            'start' => 2148,
            'end' => 2369,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2148,
              'end' => 2155,
            ),
            'kind' => 'Name',
            'value' => 'formats',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2161,
                'end' => 2171,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2161,
                  'end' => 2166,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2168,
                  'end' => 2171,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2168,
                    'end' => 2171,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2176,
                'end' => 2189,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2176,
                  'end' => 2181,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2183,
                  'end' => 2189,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2183,
                    'end' => 2189,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 2195,
                'end' => 2340,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2320,
                  'end' => 2325,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2327,
                  'end' => 2340,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2327,
                    'end' => 2340,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2195,
                  'end' => 2315,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2345,
                'end' => 2356,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2345,
                  'end' => 2351,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2353,
                  'end' => 2356,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2353,
                    'end' => 2356,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2362,
              'end' => 2369,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2363,
                'end' => 2368,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2363,
                  'end' => 2368,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        11 => 
        array (
          'loc' => 
          array (
            'start' => 2372,
            'end' => 2552,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2372,
              'end' => 2378,
            ),
            'kind' => 'Name',
            'value' => 'format',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2384,
                'end' => 2390,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2384,
                  'end' => 2386,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2388,
                  'end' => 2390,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2388,
                    'end' => 2390,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2396,
                'end' => 2541,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2521,
                  'end' => 2526,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2528,
                  'end' => 2541,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2528,
                    'end' => 2541,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2396,
                  'end' => 2516,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2547,
              'end' => 2552,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2547,
                'end' => 2552,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        12 => 
        array (
          'loc' => 
          array (
            'start' => 2555,
            'end' => 2780,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2555,
              'end' => 2566,
            ),
            'kind' => 'Name',
            'value' => 'identifiers',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2572,
                'end' => 2582,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2572,
                  'end' => 2577,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2579,
                  'end' => 2582,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2579,
                    'end' => 2582,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2587,
                'end' => 2600,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2587,
                  'end' => 2592,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2594,
                  'end' => 2600,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2594,
                    'end' => 2600,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 2606,
                'end' => 2751,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2731,
                  'end' => 2736,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2738,
                  'end' => 2751,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2738,
                    'end' => 2751,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2606,
                  'end' => 2726,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2756,
                'end' => 2767,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2756,
                  'end' => 2762,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2764,
                  'end' => 2767,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2764,
                    'end' => 2767,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2773,
              'end' => 2780,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2774,
                'end' => 2779,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2774,
                  'end' => 2779,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        13 => 
        array (
          'loc' => 
          array (
            'start' => 2783,
            'end' => 2967,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2783,
              'end' => 2793,
            ),
            'kind' => 'Name',
            'value' => 'identifier',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2799,
                'end' => 2805,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2799,
                  'end' => 2801,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2803,
                  'end' => 2805,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2803,
                    'end' => 2805,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 2811,
                'end' => 2956,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2936,
                  'end' => 2941,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2943,
                  'end' => 2956,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2943,
                    'end' => 2956,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 2811,
                  'end' => 2931,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2962,
              'end' => 2967,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2962,
                'end' => 2967,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        14 => 
        array (
          'loc' => 
          array (
            'start' => 2970,
            'end' => 3193,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2970,
              'end' => 2979,
            ),
            'kind' => 'Name',
            'value' => 'languages',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 2985,
                'end' => 2995,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2985,
                  'end' => 2990,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2992,
                  'end' => 2995,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2992,
                    'end' => 2995,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3000,
                'end' => 3013,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3000,
                  'end' => 3005,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3007,
                  'end' => 3013,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3007,
                    'end' => 3013,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3019,
                'end' => 3164,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3144,
                  'end' => 3149,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3151,
                  'end' => 3164,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3151,
                    'end' => 3164,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3019,
                  'end' => 3139,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3169,
                'end' => 3180,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3169,
                  'end' => 3175,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3177,
                  'end' => 3180,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3177,
                    'end' => 3180,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3186,
              'end' => 3193,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3187,
                'end' => 3192,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3187,
                  'end' => 3192,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        15 => 
        array (
          'loc' => 
          array (
            'start' => 3196,
            'end' => 3378,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3196,
              'end' => 3204,
            ),
            'kind' => 'Name',
            'value' => 'language',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3210,
                'end' => 3216,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3210,
                  'end' => 3212,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3214,
                  'end' => 3216,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3214,
                    'end' => 3216,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3222,
                'end' => 3367,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3347,
                  'end' => 3352,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3354,
                  'end' => 3367,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3354,
                    'end' => 3367,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3222,
                  'end' => 3342,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3373,
              'end' => 3378,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3373,
                'end' => 3378,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        16 => 
        array (
          'loc' => 
          array (
            'start' => 3381,
            'end' => 3606,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3381,
              'end' => 3392,
            ),
            'kind' => 'Name',
            'value' => 'preferences',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3398,
                'end' => 3408,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3398,
                  'end' => 3403,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3405,
                  'end' => 3408,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3405,
                    'end' => 3408,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3413,
                'end' => 3426,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3413,
                  'end' => 3418,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3420,
                  'end' => 3426,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3420,
                    'end' => 3426,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3432,
                'end' => 3577,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3557,
                  'end' => 3562,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3564,
                  'end' => 3577,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3564,
                    'end' => 3577,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3432,
                  'end' => 3552,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3582,
                'end' => 3593,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3582,
                  'end' => 3588,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3590,
                  'end' => 3593,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3590,
                    'end' => 3593,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3599,
              'end' => 3606,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3600,
                'end' => 3605,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3600,
                  'end' => 3605,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        17 => 
        array (
          'loc' => 
          array (
            'start' => 3609,
            'end' => 3793,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3609,
              'end' => 3619,
            ),
            'kind' => 'Name',
            'value' => 'preference',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3625,
                'end' => 3631,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3625,
                  'end' => 3627,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3629,
                  'end' => 3631,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3629,
                    'end' => 3631,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3637,
                'end' => 3782,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3762,
                  'end' => 3767,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3769,
                  'end' => 3782,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3769,
                    'end' => 3782,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3637,
                  'end' => 3757,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3788,
              'end' => 3793,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3788,
                'end' => 3793,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        18 => 
        array (
          'loc' => 
          array (
            'start' => 3796,
            'end' => 4020,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3796,
              'end' => 3806,
            ),
            'kind' => 'Name',
            'value' => 'publishers',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 3812,
                'end' => 3822,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3812,
                  'end' => 3817,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3819,
                  'end' => 3822,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3819,
                    'end' => 3822,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 3827,
                'end' => 3840,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3827,
                  'end' => 3832,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3834,
                  'end' => 3840,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3834,
                    'end' => 3840,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 3846,
                'end' => 3991,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3971,
                  'end' => 3976,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3978,
                  'end' => 3991,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3978,
                    'end' => 3991,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 3846,
                  'end' => 3966,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3996,
                'end' => 4007,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3996,
                  'end' => 4002,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4004,
                  'end' => 4007,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4004,
                    'end' => 4007,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4013,
              'end' => 4020,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4014,
                'end' => 4019,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4014,
                  'end' => 4019,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        19 => 
        array (
          'loc' => 
          array (
            'start' => 4023,
            'end' => 4206,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4023,
              'end' => 4032,
            ),
            'kind' => 'Name',
            'value' => 'publisher',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4038,
                'end' => 4044,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4038,
                  'end' => 4040,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4042,
                  'end' => 4044,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4042,
                    'end' => 4044,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4050,
                'end' => 4195,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4175,
                  'end' => 4180,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4182,
                  'end' => 4195,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4182,
                    'end' => 4195,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4050,
                  'end' => 4170,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4201,
              'end' => 4206,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4201,
                'end' => 4206,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        20 => 
        array (
          'loc' => 
          array (
            'start' => 4209,
            'end' => 4430,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4209,
              'end' => 4216,
            ),
            'kind' => 'Name',
            'value' => 'ratings',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4222,
                'end' => 4232,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4222,
                  'end' => 4227,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4229,
                  'end' => 4232,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4229,
                    'end' => 4232,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4237,
                'end' => 4250,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4237,
                  'end' => 4242,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4244,
                  'end' => 4250,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4244,
                    'end' => 4250,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 4256,
                'end' => 4401,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4381,
                  'end' => 4386,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4388,
                  'end' => 4401,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4388,
                    'end' => 4401,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4256,
                  'end' => 4376,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4406,
                'end' => 4417,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4406,
                  'end' => 4412,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4414,
                  'end' => 4417,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4414,
                    'end' => 4417,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4423,
              'end' => 4430,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4424,
                'end' => 4429,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4424,
                  'end' => 4429,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        21 => 
        array (
          'loc' => 
          array (
            'start' => 4433,
            'end' => 4613,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4433,
              'end' => 4439,
            ),
            'kind' => 'Name',
            'value' => 'rating',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4445,
                'end' => 4451,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4445,
                  'end' => 4447,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4449,
                  'end' => 4451,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4449,
                    'end' => 4451,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4457,
                'end' => 4602,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4582,
                  'end' => 4587,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4589,
                  'end' => 4602,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4589,
                    'end' => 4602,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4457,
                  'end' => 4577,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4608,
              'end' => 4613,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4608,
                'end' => 4613,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        22 => 
        array (
          'loc' => 
          array (
            'start' => 4616,
            'end' => 4836,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4616,
              'end' => 4622,
            ),
            'kind' => 'Name',
            'value' => 'series',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4628,
                'end' => 4638,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4628,
                  'end' => 4633,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4635,
                  'end' => 4638,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4635,
                    'end' => 4638,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4643,
                'end' => 4656,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4643,
                  'end' => 4648,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4650,
                  'end' => 4656,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4650,
                    'end' => 4656,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 4662,
                'end' => 4807,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4787,
                  'end' => 4792,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4794,
                  'end' => 4807,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4794,
                    'end' => 4807,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4662,
                  'end' => 4782,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4812,
                'end' => 4823,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4812,
                  'end' => 4818,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4820,
                  'end' => 4823,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4820,
                    'end' => 4823,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4829,
              'end' => 4836,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4830,
                'end' => 4835,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4830,
                  'end' => 4835,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        23 => 
        array (
          'loc' => 
          array (
            'start' => 4839,
            'end' => 5018,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4839,
              'end' => 4844,
            ),
            'kind' => 'Name',
            'value' => 'serie',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4850,
                'end' => 4856,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4850,
                  'end' => 4852,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4854,
                  'end' => 4856,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4854,
                    'end' => 4856,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 4862,
                'end' => 5007,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4987,
                  'end' => 4992,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4994,
                  'end' => 5007,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4994,
                    'end' => 5007,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 4862,
                  'end' => 4982,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5013,
              'end' => 5018,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5013,
                'end' => 5018,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        24 => 
        array (
          'loc' => 
          array (
            'start' => 5021,
            'end' => 5239,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5021,
              'end' => 5025,
            ),
            'kind' => 'Name',
            'value' => 'tags',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 5031,
                'end' => 5041,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5031,
                  'end' => 5036,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5038,
                  'end' => 5041,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5038,
                    'end' => 5041,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 5046,
                'end' => 5059,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5046,
                  'end' => 5051,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5053,
                  'end' => 5059,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5053,
                    'end' => 5059,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 5065,
                'end' => 5210,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5190,
                  'end' => 5195,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5197,
                  'end' => 5210,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5197,
                    'end' => 5210,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 5065,
                  'end' => 5185,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 5215,
                'end' => 5226,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5215,
                  'end' => 5221,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5223,
                  'end' => 5226,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5223,
                    'end' => 5226,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5232,
              'end' => 5239,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5233,
                'end' => 5238,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5233,
                  'end' => 5238,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        25 => 
        array (
          'loc' => 
          array (
            'start' => 5242,
            'end' => 5419,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5242,
              'end' => 5245,
            ),
            'kind' => 'Name',
            'value' => 'tag',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 5251,
                'end' => 5257,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5251,
                  'end' => 5253,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5255,
                  'end' => 5257,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5255,
                    'end' => 5257,
                  ),
                  'kind' => 'Name',
                  'value' => 'ID',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 5263,
                'end' => 5408,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5388,
                  'end' => 5393,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5395,
                  'end' => 5408,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5395,
                    'end' => 5408,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 5263,
                  'end' => 5383,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5414,
              'end' => 5419,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5414,
                'end' => 5419,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        26 => 
        array (
          'loc' => 
          array (
            'start' => 5422,
            'end' => 5586,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5559,
              'end' => 5563,
            ),
            'kind' => 'Name',
            'value' => 'node',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 5569,
                'end' => 5576,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5569,
                  'end' => 5571,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5573,
                  'end' => 5576,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5573,
                    'end' => 5575,
                  ),
                  'kind' => 'NamedType',
                  'name' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 5573,
                      'end' => 5575,
                    ),
                    'kind' => 'Name',
                    'value' => 'ID',
                  ),
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5582,
              'end' => 5586,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5582,
                'end' => 5586,
              ),
              'kind' => 'Name',
              'value' => 'Node',
            ),
          ),
          'directives' => 
          array (
          ),
          'description' => 
          array (
            'loc' => 
            array (
              'start' => 5422,
              'end' => 5556,
            ),
            'kind' => 'StringValue',
            'value' => 'Node root field with Global Object Identifier
See https://relay.dev/graphql/objectidentification.htm#sec-Node-root-field',
            'block' => true,
          ),
        ),
        27 => 
        array (
          'loc' => 
          array (
            'start' => 5589,
            'end' => 5765,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5725,
              'end' => 5733,
            ),
            'kind' => 'Name',
            'value' => 'nodelist',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 5739,
                'end' => 5753,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5739,
                  'end' => 5745,
                ),
                'kind' => 'Name',
                'value' => 'idlist',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5747,
                  'end' => 5753,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5747,
                    'end' => 5752,
                  ),
                  'kind' => 'ListType',
                  'type' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 5748,
                      'end' => 5751,
                    ),
                    'kind' => 'NonNullType',
                    'type' => 
                    array (
                      'loc' => 
                      array (
                        'start' => 5748,
                        'end' => 5750,
                      ),
                      'kind' => 'NamedType',
                      'name' => 
                      array (
                        'loc' => 
                        array (
                          'start' => 5748,
                          'end' => 5750,
                        ),
                        'kind' => 'Name',
                        'value' => 'ID',
                      ),
                    ),
                  ),
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5759,
              'end' => 5765,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5760,
                'end' => 5764,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5760,
                  'end' => 5764,
                ),
                'kind' => 'Name',
                'value' => 'Node',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
          'description' => 
          array (
            'loc' => 
            array (
              'start' => 5589,
              'end' => 5722,
            ),
            'kind' => 'StringValue',
            'value' => 'Plural identifying root field
See https://relay.dev/graphql/objectidentification.htm#sec-Plural-identifying-root-fields',
            'block' => true,
          ),
        ),
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 0,
          'end' => 125,
        ),
        'kind' => 'StringValue',
        'value' => 'Adapted from https://github.com/mikespub-org/acdibble-tuql
Goal: create GraphQL interface to Calibre database (maybe)',
        'block' => true,
      ),
    ),
    1 => 
    array (
      'loc' => 
      array (
        'start' => 5769,
        'end' => 5849,
      ),
      'kind' => 'ScalarTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5836,
          'end' => 5849,
        ),
        'kind' => 'Name',
        'value' => 'SequelizeJSON',
      ),
      'directives' => 
      array (
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 5769,
          'end' => 5828,
        ),
        'kind' => 'StringValue',
        'value' => 'The `JSON` scalar type represents raw JSON as values.',
        'block' => true,
      ),
    ),
    2 => 
    array (
      'loc' => 
      array (
        'start' => 5851,
        'end' => 6006,
      ),
      'kind' => 'InterfaceTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5988,
          'end' => 5992,
        ),
        'kind' => 'Name',
        'value' => 'Node',
      ),
      'directives' => 
      array (
      ),
      'interfaces' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 5997,
            'end' => 6004,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5997,
              'end' => 5999,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6001,
              'end' => 6004,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6001,
                'end' => 6003,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6001,
                  'end' => 6003,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
      'description' => 
      array (
        'loc' => 
        array (
          'start' => 5851,
          'end' => 5977,
        ),
        'kind' => 'StringValue',
        'value' => 'Node Interface with Global Object Identifier
See https://relay.dev/graphql/objectidentification.htm#sec-Node-Interface',
        'block' => true,
      ),
    ),
    3 => 
    array (
      'loc' => 
      array (
        'start' => 6008,
        'end' => 6397,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6013,
          'end' => 6018,
        ),
        'kind' => 'Name',
        'value' => 'Entry',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6030,
            'end' => 6034,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6030,
              'end' => 6034,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6039,
            'end' => 6046,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6039,
              'end' => 6041,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6043,
              'end' => 6046,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6043,
                'end' => 6045,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6043,
                  'end' => 6045,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6049,
            'end' => 6063,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6049,
              'end' => 6054,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6056,
              'end' => 6063,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6056,
                'end' => 6062,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6056,
                  'end' => 6062,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6066,
            'end' => 6081,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6066,
              'end' => 6073,
            ),
            'kind' => 'Name',
            'value' => 'content',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6075,
              'end' => 6081,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6075,
                'end' => 6081,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6084,
            'end' => 6103,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6084,
              'end' => 6095,
            ),
            'kind' => 'Name',
            'value' => 'contentType',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6097,
              'end' => 6103,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6097,
                'end' => 6103,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 6106,
            'end' => 6123,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6106,
              'end' => 6115,
            ),
            'kind' => 'Name',
            'value' => 'linkArray',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6117,
              'end' => 6123,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6118,
                'end' => 6122,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6118,
                  'end' => 6122,
                ),
                'kind' => 'Name',
                'value' => 'Link',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 6126,
            'end' => 6143,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6126,
              'end' => 6135,
            ),
            'kind' => 'Name',
            'value' => 'className',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6137,
              'end' => 6143,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6137,
                'end' => 6143,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 6146,
            'end' => 6169,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6146,
              'end' => 6161,
            ),
            'kind' => 'Name',
            'value' => 'numberOfElement',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6163,
              'end' => 6169,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6163,
                'end' => 6169,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 6172,
            'end' => 6395,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6172,
              'end' => 6177,
            ),
            'kind' => 'Name',
            'value' => 'books',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 6183,
                'end' => 6193,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6183,
                  'end' => 6188,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 6190,
                  'end' => 6193,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 6190,
                    'end' => 6193,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            1 => 
            array (
              'loc' => 
              array (
                'start' => 6198,
                'end' => 6211,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6198,
                  'end' => 6203,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 6205,
                  'end' => 6211,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 6205,
                    'end' => 6211,
                  ),
                  'kind' => 'Name',
                  'value' => 'String',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
            2 => 
            array (
              'loc' => 
              array (
                'start' => 6217,
                'end' => 6362,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6342,
                  'end' => 6347,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 6349,
                  'end' => 6362,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 6349,
                    'end' => 6362,
                  ),
                  'kind' => 'Name',
                  'value' => 'SequelizeJSON',
                ),
              ),
              'directives' => 
              array (
              ),
              'description' => 
              array (
                'loc' => 
                array (
                  'start' => 6217,
                  'end' => 6337,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON object conforming the the shape specified in http://docs.sequelizejs.com/en/latest/docs/querying/',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 6367,
                'end' => 6378,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6367,
                  'end' => 6373,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 6375,
                  'end' => 6378,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 6375,
                    'end' => 6378,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
                ),
              ),
              'directives' => 
              array (
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6384,
              'end' => 6395,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6385,
                'end' => 6394,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6385,
                  'end' => 6394,
                ),
                'kind' => 'Name',
                'value' => 'EntryBook',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    4 => 
    array (
      'loc' => 
      array (
        'start' => 6399,
        'end' => 6770,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6404,
          'end' => 6413,
        ),
        'kind' => 'Name',
        'value' => 'EntryBook',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6425,
            'end' => 6429,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6425,
              'end' => 6429,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6434,
            'end' => 6441,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6434,
              'end' => 6436,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6438,
              'end' => 6441,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6438,
                'end' => 6440,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6438,
                  'end' => 6440,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6444,
            'end' => 6458,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6444,
              'end' => 6449,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6451,
              'end' => 6458,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6451,
                'end' => 6457,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6451,
                  'end' => 6457,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6461,
            'end' => 6476,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6461,
              'end' => 6468,
            ),
            'kind' => 'Name',
            'value' => 'content',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6470,
              'end' => 6476,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6470,
                'end' => 6476,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6479,
            'end' => 6498,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6479,
              'end' => 6490,
            ),
            'kind' => 'Name',
            'value' => 'contentType',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6492,
              'end' => 6498,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6492,
                'end' => 6498,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 6501,
            'end' => 6518,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6501,
              'end' => 6510,
            ),
            'kind' => 'Name',
            'value' => 'linkArray',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6512,
              'end' => 6518,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6513,
                'end' => 6517,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6513,
                  'end' => 6517,
                ),
                'kind' => 'Name',
                'value' => 'Link',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        5 => 
        array (
          'loc' => 
          array (
            'start' => 6521,
            'end' => 6538,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6521,
              'end' => 6530,
            ),
            'kind' => 'Name',
            'value' => 'className',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6532,
              'end' => 6538,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6532,
                'end' => 6538,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        6 => 
        array (
          'loc' => 
          array (
            'start' => 6541,
            'end' => 6564,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6541,
              'end' => 6556,
            ),
            'kind' => 'Name',
            'value' => 'numberOfElement',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6558,
              'end' => 6564,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6558,
                'end' => 6564,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        7 => 
        array (
          'loc' => 
          array (
            'start' => 6567,
            'end' => 6579,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6567,
              'end' => 6571,
            ),
            'kind' => 'Name',
            'value' => 'path',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6573,
              'end' => 6579,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6573,
                'end' => 6579,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        8 => 
        array (
          'loc' => 
          array (
            'start' => 6582,
            'end' => 6598,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6582,
              'end' => 6589,
            ),
            'kind' => 'Name',
            'value' => 'authors',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6591,
              'end' => 6598,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6592,
                'end' => 6597,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6592,
                  'end' => 6597,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        9 => 
        array (
          'loc' => 
          array (
            'start' => 6601,
            'end' => 6623,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6601,
              'end' => 6614,
            ),
            'kind' => 'Name',
            'value' => 'customColumns',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6616,
              'end' => 6623,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6617,
                'end' => 6622,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6617,
                  'end' => 6622,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        10 => 
        array (
          'loc' => 
          array (
            'start' => 6626,
            'end' => 6639,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6626,
              'end' => 6631,
            ),
            'kind' => 'Name',
            'value' => 'datas',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6633,
              'end' => 6639,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6634,
                'end' => 6638,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6634,
                  'end' => 6638,
                ),
                'kind' => 'Name',
                'value' => 'Data',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        11 => 
        array (
          'loc' => 
          array (
            'start' => 6642,
            'end' => 6658,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6642,
              'end' => 6649,
            ),
            'kind' => 'Name',
            'value' => 'formats',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6651,
              'end' => 6658,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6652,
                'end' => 6657,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6652,
                  'end' => 6657,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        12 => 
        array (
          'loc' => 
          array (
            'start' => 6661,
            'end' => 6681,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6661,
              'end' => 6672,
            ),
            'kind' => 'Name',
            'value' => 'identifiers',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6674,
              'end' => 6681,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6675,
                'end' => 6680,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6675,
                  'end' => 6680,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        13 => 
        array (
          'loc' => 
          array (
            'start' => 6684,
            'end' => 6701,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6684,
              'end' => 6693,
            ),
            'kind' => 'Name',
            'value' => 'languages',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6695,
              'end' => 6701,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6695,
                'end' => 6701,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        14 => 
        array (
          'loc' => 
          array (
            'start' => 6704,
            'end' => 6720,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6704,
              'end' => 6713,
            ),
            'kind' => 'Name',
            'value' => 'publisher',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6715,
              'end' => 6720,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6715,
                'end' => 6720,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        15 => 
        array (
          'loc' => 
          array (
            'start' => 6723,
            'end' => 6737,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6723,
              'end' => 6729,
            ),
            'kind' => 'Name',
            'value' => 'rating',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6731,
              'end' => 6737,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6731,
                'end' => 6737,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        16 => 
        array (
          'loc' => 
          array (
            'start' => 6740,
            'end' => 6752,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6740,
              'end' => 6745,
            ),
            'kind' => 'Name',
            'value' => 'serie',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6747,
              'end' => 6752,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6747,
                'end' => 6752,
              ),
              'kind' => 'Name',
              'value' => 'Entry',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        17 => 
        array (
          'loc' => 
          array (
            'start' => 6755,
            'end' => 6768,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6755,
              'end' => 6759,
            ),
            'kind' => 'Name',
            'value' => 'tags',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6761,
              'end' => 6768,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6762,
                'end' => 6767,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6762,
                  'end' => 6767,
                ),
                'kind' => 'Name',
                'value' => 'Entry',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    5 => 
    array (
      'loc' => 
      array (
        'start' => 6772,
        'end' => 6847,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6777,
          'end' => 6781,
        ),
        'kind' => 'Name',
        'value' => 'Link',
      ),
      'interfaces' => 
      array (
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6786,
            'end' => 6799,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6786,
              'end' => 6790,
            ),
            'kind' => 'Name',
            'value' => 'href',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6792,
              'end' => 6799,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6792,
                'end' => 6798,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6792,
                  'end' => 6798,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6802,
            'end' => 6815,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6802,
              'end' => 6806,
            ),
            'kind' => 'Name',
            'value' => 'type',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6808,
              'end' => 6815,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6808,
                'end' => 6814,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6808,
                  'end' => 6814,
                ),
                'kind' => 'Name',
                'value' => 'String',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6818,
            'end' => 6829,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6818,
              'end' => 6821,
            ),
            'kind' => 'Name',
            'value' => 'rel',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6823,
              'end' => 6829,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6823,
                'end' => 6829,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6832,
            'end' => 6845,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6832,
              'end' => 6837,
            ),
            'kind' => 'Name',
            'value' => 'title',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6839,
              'end' => 6845,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6839,
                'end' => 6845,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    6 => 
    array (
      'loc' => 
      array (
        'start' => 6849,
        'end' => 6962,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6854,
          'end' => 6858,
        ),
        'kind' => 'Name',
        'value' => 'Data',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6870,
            'end' => 6874,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6870,
              'end' => 6874,
            ),
            'kind' => 'Name',
            'value' => 'Node',
          ),
        ),
      ),
      'directives' => 
      array (
      ),
      'fields' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6879,
            'end' => 6886,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6879,
              'end' => 6881,
            ),
            'kind' => 'Name',
            'value' => 'id',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6883,
              'end' => 6886,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6883,
                'end' => 6885,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6883,
                  'end' => 6885,
                ),
                'kind' => 'Name',
                'value' => 'ID',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6889,
            'end' => 6904,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6889,
              'end' => 6893,
            ),
            'kind' => 'Name',
            'value' => 'book',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6895,
              'end' => 6904,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6895,
                'end' => 6904,
              ),
              'kind' => 'Name',
              'value' => 'EntryBook',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6907,
            'end' => 6921,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6907,
              'end' => 6913,
            ),
            'kind' => 'Name',
            'value' => 'format',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6915,
              'end' => 6921,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6915,
                'end' => 6921,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6924,
            'end' => 6945,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6924,
              'end' => 6940,
            ),
            'kind' => 'Name',
            'value' => 'uncompressedSize',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6942,
              'end' => 6945,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6942,
                'end' => 6945,
              ),
              'kind' => 'Name',
              'value' => 'Int',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
        4 => 
        array (
          'loc' => 
          array (
            'start' => 6948,
            'end' => 6960,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6948,
              'end' => 6952,
            ),
            'kind' => 'Name',
            'value' => 'name',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6954,
              'end' => 6960,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6954,
                'end' => 6960,
              ),
              'kind' => 'Name',
              'value' => 'String',
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
  ),
);
