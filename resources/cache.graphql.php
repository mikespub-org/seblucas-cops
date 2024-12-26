<?php
return array (
  'loc' => 
  array (
    'start' => 0,
    'end' => 6311,
  ),
  'kind' => 'Document',
  'definitions' => 
  array (
    0 => 
    array (
      'loc' => 
      array (
        'start' => 0,
        'end' => 4796,
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
            'end' => 327,
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
                'end' => 298,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 278,
                  'end' => 283,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 285,
                  'end' => 298,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 285,
                    'end' => 298,
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
                  'end' => 273,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 303,
                'end' => 314,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 303,
                  'end' => 309,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 311,
                  'end' => 314,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 311,
                    'end' => 314,
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
              'start' => 320,
              'end' => 327,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 321,
                'end' => 326,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 321,
                  'end' => 326,
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
            'start' => 330,
            'end' => 475,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 330,
              'end' => 336,
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
                'start' => 342,
                'end' => 348,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 342,
                  'end' => 344,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 346,
                  'end' => 348,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 346,
                    'end' => 348,
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
                'start' => 354,
                'end' => 464,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 444,
                  'end' => 449,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 451,
                  'end' => 464,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 451,
                    'end' => 464,
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
                  'start' => 354,
                  'end' => 439,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 470,
              'end' => 475,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 470,
                'end' => 475,
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
            'start' => 478,
            'end' => 666,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 478,
              'end' => 483,
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
                'start' => 489,
                'end' => 499,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 489,
                  'end' => 494,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 496,
                  'end' => 499,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 496,
                    'end' => 499,
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
                'start' => 504,
                'end' => 517,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 504,
                  'end' => 509,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 511,
                  'end' => 517,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 511,
                    'end' => 517,
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
                'start' => 523,
                'end' => 633,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 613,
                  'end' => 618,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 620,
                  'end' => 633,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 620,
                    'end' => 633,
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
                  'start' => 523,
                  'end' => 608,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 638,
                'end' => 649,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 638,
                  'end' => 644,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 646,
                  'end' => 649,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 646,
                    'end' => 649,
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
              'start' => 655,
              'end' => 666,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 656,
                'end' => 665,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 656,
                  'end' => 665,
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
            'start' => 669,
            'end' => 816,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 669,
              'end' => 673,
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
                'start' => 679,
                'end' => 685,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 679,
                  'end' => 681,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 683,
                  'end' => 685,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 683,
                    'end' => 685,
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
                'start' => 691,
                'end' => 801,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 781,
                  'end' => 786,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 788,
                  'end' => 801,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 788,
                    'end' => 801,
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
                  'start' => 691,
                  'end' => 776,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 807,
              'end' => 816,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 807,
                'end' => 816,
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
            'start' => 819,
            'end' => 1011,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 819,
              'end' => 832,
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
                'start' => 838,
                'end' => 848,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 838,
                  'end' => 843,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 845,
                  'end' => 848,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 845,
                    'end' => 848,
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
                'start' => 853,
                'end' => 866,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 853,
                  'end' => 858,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 860,
                  'end' => 866,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 860,
                    'end' => 866,
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
                'start' => 872,
                'end' => 982,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 962,
                  'end' => 967,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 969,
                  'end' => 982,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 969,
                    'end' => 982,
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
                  'start' => 872,
                  'end' => 957,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 987,
                'end' => 998,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 987,
                  'end' => 993,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 995,
                  'end' => 998,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 995,
                    'end' => 998,
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
              'start' => 1004,
              'end' => 1011,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1005,
                'end' => 1010,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1005,
                  'end' => 1010,
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
            'start' => 1014,
            'end' => 1165,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1014,
              'end' => 1026,
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
                'start' => 1032,
                'end' => 1038,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1032,
                  'end' => 1034,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1036,
                  'end' => 1038,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1036,
                    'end' => 1038,
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
                'start' => 1044,
                'end' => 1154,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1134,
                  'end' => 1139,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1141,
                  'end' => 1154,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1141,
                    'end' => 1154,
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
                  'start' => 1044,
                  'end' => 1129,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1160,
              'end' => 1165,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1160,
                'end' => 1165,
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
            'start' => 1168,
            'end' => 1317,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1168,
              'end' => 1173,
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
                'start' => 1179,
                'end' => 1189,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1179,
                  'end' => 1185,
                ),
                'kind' => 'Name',
                'value' => 'bookId',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1187,
                  'end' => 1189,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1187,
                    'end' => 1189,
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
                'start' => 1195,
                'end' => 1305,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1285,
                  'end' => 1290,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1292,
                  'end' => 1305,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1292,
                    'end' => 1305,
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
                  'start' => 1195,
                  'end' => 1280,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1311,
              'end' => 1317,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1312,
                'end' => 1316,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1312,
                  'end' => 1316,
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
            'start' => 1320,
            'end' => 1462,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1320,
              'end' => 1324,
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
                'start' => 1330,
                'end' => 1336,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1330,
                  'end' => 1332,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1334,
                  'end' => 1336,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1334,
                    'end' => 1336,
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
                'start' => 1342,
                'end' => 1452,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1432,
                  'end' => 1437,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1439,
                  'end' => 1452,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1439,
                    'end' => 1452,
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
                  'start' => 1342,
                  'end' => 1427,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1458,
              'end' => 1462,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1458,
                'end' => 1462,
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
            'start' => 1465,
            'end' => 1651,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1465,
              'end' => 1472,
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
                'start' => 1478,
                'end' => 1488,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1478,
                  'end' => 1483,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1485,
                  'end' => 1488,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1485,
                    'end' => 1488,
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
                'start' => 1493,
                'end' => 1506,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1493,
                  'end' => 1498,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1500,
                  'end' => 1506,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1500,
                    'end' => 1506,
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
                'start' => 1512,
                'end' => 1622,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1602,
                  'end' => 1607,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1609,
                  'end' => 1622,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1609,
                    'end' => 1622,
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
                  'start' => 1512,
                  'end' => 1597,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1627,
                'end' => 1638,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1627,
                  'end' => 1633,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1635,
                  'end' => 1638,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1635,
                    'end' => 1638,
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
              'start' => 1644,
              'end' => 1651,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1645,
                'end' => 1650,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1645,
                  'end' => 1650,
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
            'start' => 1654,
            'end' => 1799,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1654,
              'end' => 1660,
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
                'start' => 1666,
                'end' => 1672,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1666,
                  'end' => 1668,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1670,
                  'end' => 1672,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1670,
                    'end' => 1672,
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
                'start' => 1678,
                'end' => 1788,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1768,
                  'end' => 1773,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1775,
                  'end' => 1788,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1775,
                    'end' => 1788,
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
                  'start' => 1678,
                  'end' => 1763,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 1794,
              'end' => 1799,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 1794,
                'end' => 1799,
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
            'start' => 1802,
            'end' => 1992,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1802,
              'end' => 1813,
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
                'start' => 1819,
                'end' => 1829,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1819,
                  'end' => 1824,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1826,
                  'end' => 1829,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1826,
                    'end' => 1829,
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
                'start' => 1834,
                'end' => 1847,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1834,
                  'end' => 1839,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1841,
                  'end' => 1847,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1841,
                    'end' => 1847,
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
                'start' => 1853,
                'end' => 1963,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1943,
                  'end' => 1948,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1950,
                  'end' => 1963,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1950,
                    'end' => 1963,
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
                  'start' => 1853,
                  'end' => 1938,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 1968,
                'end' => 1979,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1968,
                  'end' => 1974,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 1976,
                  'end' => 1979,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 1976,
                    'end' => 1979,
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
              'start' => 1985,
              'end' => 1992,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 1986,
                'end' => 1991,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 1986,
                  'end' => 1991,
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
            'start' => 1995,
            'end' => 2144,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 1995,
              'end' => 2005,
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
                'start' => 2011,
                'end' => 2017,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2011,
                  'end' => 2013,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2015,
                  'end' => 2017,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2015,
                    'end' => 2017,
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
                'start' => 2023,
                'end' => 2133,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2113,
                  'end' => 2118,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2120,
                  'end' => 2133,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2120,
                    'end' => 2133,
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
                  'start' => 2023,
                  'end' => 2108,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2139,
              'end' => 2144,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2139,
                'end' => 2144,
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
            'start' => 2147,
            'end' => 2335,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2147,
              'end' => 2156,
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
                'start' => 2162,
                'end' => 2172,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2162,
                  'end' => 2167,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2169,
                  'end' => 2172,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2169,
                    'end' => 2172,
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
                'start' => 2177,
                'end' => 2190,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2177,
                  'end' => 2182,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2184,
                  'end' => 2190,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2184,
                    'end' => 2190,
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
                'start' => 2196,
                'end' => 2306,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2286,
                  'end' => 2291,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2293,
                  'end' => 2306,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2293,
                    'end' => 2306,
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
                  'start' => 2196,
                  'end' => 2281,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2311,
                'end' => 2322,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2311,
                  'end' => 2317,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2319,
                  'end' => 2322,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2319,
                    'end' => 2322,
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
              'start' => 2328,
              'end' => 2335,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2329,
                'end' => 2334,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2329,
                  'end' => 2334,
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
            'start' => 2338,
            'end' => 2485,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2338,
              'end' => 2346,
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
                'start' => 2352,
                'end' => 2358,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2352,
                  'end' => 2354,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2356,
                  'end' => 2358,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2356,
                    'end' => 2358,
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
                'start' => 2364,
                'end' => 2474,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2454,
                  'end' => 2459,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2461,
                  'end' => 2474,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2461,
                    'end' => 2474,
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
                  'start' => 2364,
                  'end' => 2449,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2480,
              'end' => 2485,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2480,
                'end' => 2485,
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
            'start' => 2488,
            'end' => 2678,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2488,
              'end' => 2499,
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
                'start' => 2505,
                'end' => 2515,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2505,
                  'end' => 2510,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2512,
                  'end' => 2515,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2512,
                    'end' => 2515,
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
                'start' => 2520,
                'end' => 2533,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2520,
                  'end' => 2525,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2527,
                  'end' => 2533,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2527,
                    'end' => 2533,
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
                'start' => 2539,
                'end' => 2649,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2629,
                  'end' => 2634,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2636,
                  'end' => 2649,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2636,
                    'end' => 2649,
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
                  'start' => 2539,
                  'end' => 2624,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2654,
                'end' => 2665,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2654,
                  'end' => 2660,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2662,
                  'end' => 2665,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2662,
                    'end' => 2665,
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
              'start' => 2671,
              'end' => 2678,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 2672,
                'end' => 2677,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2672,
                  'end' => 2677,
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
            'start' => 2681,
            'end' => 2830,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2681,
              'end' => 2691,
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
                'start' => 2697,
                'end' => 2703,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2697,
                  'end' => 2699,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2701,
                  'end' => 2703,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2701,
                    'end' => 2703,
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
                'start' => 2709,
                'end' => 2819,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2799,
                  'end' => 2804,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2806,
                  'end' => 2819,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2806,
                    'end' => 2819,
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
                  'start' => 2709,
                  'end' => 2794,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 2825,
              'end' => 2830,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 2825,
                'end' => 2830,
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
            'start' => 2833,
            'end' => 3022,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 2833,
              'end' => 2843,
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
                'start' => 2849,
                'end' => 2859,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2849,
                  'end' => 2854,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2856,
                  'end' => 2859,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2856,
                    'end' => 2859,
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
                'start' => 2864,
                'end' => 2877,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2864,
                  'end' => 2869,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2871,
                  'end' => 2877,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2871,
                    'end' => 2877,
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
                'start' => 2883,
                'end' => 2993,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2973,
                  'end' => 2978,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 2980,
                  'end' => 2993,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 2980,
                    'end' => 2993,
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
                  'start' => 2883,
                  'end' => 2968,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 2998,
                'end' => 3009,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 2998,
                  'end' => 3004,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3006,
                  'end' => 3009,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3006,
                    'end' => 3009,
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
              'start' => 3015,
              'end' => 3022,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3016,
                'end' => 3021,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3016,
                  'end' => 3021,
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
            'start' => 3025,
            'end' => 3173,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3025,
              'end' => 3034,
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
                'start' => 3040,
                'end' => 3046,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3040,
                  'end' => 3042,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3044,
                  'end' => 3046,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3044,
                    'end' => 3046,
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
                'start' => 3052,
                'end' => 3162,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3142,
                  'end' => 3147,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3149,
                  'end' => 3162,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3149,
                    'end' => 3162,
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
                  'start' => 3052,
                  'end' => 3137,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3168,
              'end' => 3173,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3168,
                'end' => 3173,
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
            'start' => 3176,
            'end' => 3362,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3176,
              'end' => 3183,
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
                'start' => 3189,
                'end' => 3199,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3189,
                  'end' => 3194,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3196,
                  'end' => 3199,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3196,
                    'end' => 3199,
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
                'start' => 3204,
                'end' => 3217,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3204,
                  'end' => 3209,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3211,
                  'end' => 3217,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3211,
                    'end' => 3217,
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
                'start' => 3223,
                'end' => 3333,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3313,
                  'end' => 3318,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3320,
                  'end' => 3333,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3320,
                    'end' => 3333,
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
                  'start' => 3223,
                  'end' => 3308,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3338,
                'end' => 3349,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3338,
                  'end' => 3344,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3346,
                  'end' => 3349,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3346,
                    'end' => 3349,
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
              'start' => 3355,
              'end' => 3362,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3356,
                'end' => 3361,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3356,
                  'end' => 3361,
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
            'start' => 3365,
            'end' => 3510,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3365,
              'end' => 3371,
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
                'start' => 3377,
                'end' => 3383,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3377,
                  'end' => 3379,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3381,
                  'end' => 3383,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3381,
                    'end' => 3383,
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
                'start' => 3389,
                'end' => 3499,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3479,
                  'end' => 3484,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3486,
                  'end' => 3499,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3486,
                    'end' => 3499,
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
                  'start' => 3389,
                  'end' => 3474,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3505,
              'end' => 3510,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3505,
                'end' => 3510,
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
            'start' => 3513,
            'end' => 3698,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3513,
              'end' => 3519,
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
                'start' => 3525,
                'end' => 3535,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3525,
                  'end' => 3530,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3532,
                  'end' => 3535,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3532,
                    'end' => 3535,
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
                'start' => 3540,
                'end' => 3553,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3540,
                  'end' => 3545,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3547,
                  'end' => 3553,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3547,
                    'end' => 3553,
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
                'start' => 3559,
                'end' => 3669,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3649,
                  'end' => 3654,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3656,
                  'end' => 3669,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3656,
                    'end' => 3669,
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
                  'start' => 3559,
                  'end' => 3644,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 3674,
                'end' => 3685,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3674,
                  'end' => 3680,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3682,
                  'end' => 3685,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3682,
                    'end' => 3685,
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
              'start' => 3691,
              'end' => 3698,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 3692,
                'end' => 3697,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3692,
                  'end' => 3697,
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
            'start' => 3701,
            'end' => 3845,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3701,
              'end' => 3706,
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
                'start' => 3712,
                'end' => 3718,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3712,
                  'end' => 3714,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3716,
                  'end' => 3718,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3716,
                    'end' => 3718,
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
                'start' => 3724,
                'end' => 3834,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3814,
                  'end' => 3819,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3821,
                  'end' => 3834,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3821,
                    'end' => 3834,
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
                  'start' => 3724,
                  'end' => 3809,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 3840,
              'end' => 3845,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 3840,
                'end' => 3845,
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
            'start' => 3848,
            'end' => 4031,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 3848,
              'end' => 3852,
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
                'start' => 3858,
                'end' => 3868,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3858,
                  'end' => 3863,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3865,
                  'end' => 3868,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3865,
                    'end' => 3868,
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
                'start' => 3873,
                'end' => 3886,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3873,
                  'end' => 3878,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3880,
                  'end' => 3886,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3880,
                    'end' => 3886,
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
                'start' => 3892,
                'end' => 4002,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 3982,
                  'end' => 3987,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 3989,
                  'end' => 4002,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 3989,
                    'end' => 4002,
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
                  'start' => 3892,
                  'end' => 3977,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 4007,
                'end' => 4018,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4007,
                  'end' => 4013,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4015,
                  'end' => 4018,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4015,
                    'end' => 4018,
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
              'start' => 4024,
              'end' => 4031,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4025,
                'end' => 4030,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4025,
                  'end' => 4030,
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
            'start' => 4034,
            'end' => 4176,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4034,
              'end' => 4037,
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
                'start' => 4043,
                'end' => 4049,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4043,
                  'end' => 4045,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4047,
                  'end' => 4049,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4047,
                    'end' => 4049,
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
                'start' => 4055,
                'end' => 4165,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4145,
                  'end' => 4150,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4152,
                  'end' => 4165,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4152,
                    'end' => 4165,
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
                  'start' => 4055,
                  'end' => 4140,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 4171,
              'end' => 4176,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4171,
                'end' => 4176,
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
            'start' => 4179,
            'end' => 4343,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4316,
              'end' => 4320,
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
                'start' => 4326,
                'end' => 4333,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4326,
                  'end' => 4328,
                ),
                'kind' => 'Name',
                'value' => 'id',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4330,
                  'end' => 4333,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4330,
                    'end' => 4332,
                  ),
                  'kind' => 'NamedType',
                  'name' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4330,
                      'end' => 4332,
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
              'start' => 4339,
              'end' => 4343,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 4339,
                'end' => 4343,
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
              'start' => 4179,
              'end' => 4313,
            ),
            'kind' => 'StringValue',
            'value' => 'Node root field with Global Object Identifier
See https://relay.dev/graphql/objectidentification.htm#sec-Node-root-field',
            'block' => true,
          ),
        ),
        25 => 
        array (
          'loc' => 
          array (
            'start' => 4346,
            'end' => 4522,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4482,
              'end' => 4490,
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
                'start' => 4496,
                'end' => 4510,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4496,
                  'end' => 4502,
                ),
                'kind' => 'Name',
                'value' => 'idlist',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4504,
                  'end' => 4510,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4504,
                    'end' => 4509,
                  ),
                  'kind' => 'ListType',
                  'type' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4505,
                      'end' => 4508,
                    ),
                    'kind' => 'NonNullType',
                    'type' => 
                    array (
                      'loc' => 
                      array (
                        'start' => 4505,
                        'end' => 4507,
                      ),
                      'kind' => 'NamedType',
                      'name' => 
                      array (
                        'loc' => 
                        array (
                          'start' => 4505,
                          'end' => 4507,
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
              'start' => 4516,
              'end' => 4522,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4517,
                'end' => 4521,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4517,
                  'end' => 4521,
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
              'start' => 4346,
              'end' => 4479,
            ),
            'kind' => 'StringValue',
            'value' => 'Plural identifying root field
See https://relay.dev/graphql/objectidentification.htm#sec-Plural-identifying-root-fields',
            'block' => true,
          ),
        ),
        26 => 
        array (
          'loc' => 
          array (
            'start' => 4525,
            'end' => 4794,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 4565,
              'end' => 4571,
            ),
            'kind' => 'Name',
            'value' => 'search',
          ),
          'arguments' => 
          array (
            0 => 
            array (
              'loc' => 
              array (
                'start' => 4577,
                'end' => 4591,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4577,
                  'end' => 4582,
                ),
                'kind' => 'Name',
                'value' => 'query',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4584,
                  'end' => 4591,
                ),
                'kind' => 'NonNullType',
                'type' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4584,
                    'end' => 4590,
                  ),
                  'kind' => 'NamedType',
                  'name' => 
                  array (
                    'loc' => 
                    array (
                      'start' => 4584,
                      'end' => 4590,
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
                'start' => 4596,
                'end' => 4609,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4596,
                  'end' => 4601,
                ),
                'kind' => 'Name',
                'value' => 'scope',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4603,
                  'end' => 4609,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4603,
                    'end' => 4609,
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
                'start' => 4614,
                'end' => 4624,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4614,
                  'end' => 4619,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4621,
                  'end' => 4624,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4621,
                    'end' => 4624,
                  ),
                  'kind' => 'Name',
                  'value' => 'Int',
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
                'start' => 4629,
                'end' => 4642,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4629,
                  'end' => 4634,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4636,
                  'end' => 4642,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4636,
                    'end' => 4642,
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
                'start' => 4648,
                'end' => 4758,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4738,
                  'end' => 4743,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4745,
                  'end' => 4758,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4745,
                    'end' => 4758,
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
                  'start' => 4648,
                  'end' => 4733,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            5 => 
            array (
              'loc' => 
              array (
                'start' => 4763,
                'end' => 4774,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4763,
                  'end' => 4769,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 4771,
                  'end' => 4774,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 4771,
                    'end' => 4774,
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
              'start' => 4780,
              'end' => 4794,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 4781,
                'end' => 4793,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 4781,
                  'end' => 4793,
                ),
                'kind' => 'Name',
                'value' => 'SearchResult',
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
              'start' => 4525,
              'end' => 4562,
            ),
            'kind' => 'StringValue',
            'value' => 'Search by query and scope',
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
        'start' => 4798,
        'end' => 4878,
      ),
      'kind' => 'ScalarTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 4865,
          'end' => 4878,
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
          'start' => 4798,
          'end' => 4857,
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
        'start' => 4880,
        'end' => 5035,
      ),
      'kind' => 'InterfaceTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5017,
          'end' => 5021,
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
            'start' => 5026,
            'end' => 5033,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5026,
              'end' => 5028,
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
              'start' => 5030,
              'end' => 5033,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5030,
                'end' => 5032,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5030,
                  'end' => 5032,
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
          'start' => 4880,
          'end' => 5006,
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
        'start' => 5037,
        'end' => 5442,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5042,
          'end' => 5047,
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
            'start' => 5059,
            'end' => 5063,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5059,
              'end' => 5063,
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
            'start' => 5068,
            'end' => 5075,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5068,
              'end' => 5070,
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
              'start' => 5072,
              'end' => 5075,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5072,
                'end' => 5074,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5072,
                  'end' => 5074,
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
            'start' => 5078,
            'end' => 5092,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5078,
              'end' => 5083,
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
              'start' => 5085,
              'end' => 5092,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5085,
                'end' => 5091,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5085,
                  'end' => 5091,
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
            'start' => 5095,
            'end' => 5110,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5095,
              'end' => 5102,
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
              'start' => 5104,
              'end' => 5110,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5104,
                'end' => 5110,
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
            'start' => 5113,
            'end' => 5132,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5113,
              'end' => 5124,
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
              'start' => 5126,
              'end' => 5132,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5126,
                'end' => 5132,
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
            'start' => 5135,
            'end' => 5152,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5135,
              'end' => 5144,
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
              'start' => 5146,
              'end' => 5152,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5147,
                'end' => 5151,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5147,
                  'end' => 5151,
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
            'start' => 5155,
            'end' => 5172,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5155,
              'end' => 5164,
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
              'start' => 5166,
              'end' => 5172,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5166,
                'end' => 5172,
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
            'start' => 5175,
            'end' => 5198,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5175,
              'end' => 5190,
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
              'start' => 5192,
              'end' => 5198,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5192,
                'end' => 5198,
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
            'start' => 5201,
            'end' => 5389,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5201,
              'end' => 5206,
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
                'start' => 5212,
                'end' => 5222,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5212,
                  'end' => 5217,
                ),
                'kind' => 'Name',
                'value' => 'limit',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5219,
                  'end' => 5222,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5219,
                    'end' => 5222,
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
                'start' => 5227,
                'end' => 5240,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5227,
                  'end' => 5232,
                ),
                'kind' => 'Name',
                'value' => 'order',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5234,
                  'end' => 5240,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5234,
                    'end' => 5240,
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
                'start' => 5246,
                'end' => 5356,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5336,
                  'end' => 5341,
                ),
                'kind' => 'Name',
                'value' => 'where',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5343,
                  'end' => 5356,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5343,
                    'end' => 5356,
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
                  'start' => 5246,
                  'end' => 5331,
                ),
                'kind' => 'StringValue',
                'value' => 'A JSON-encoded string containing the COPS filter params, e.g. {"a":3}',
                'block' => true,
              ),
            ),
            3 => 
            array (
              'loc' => 
              array (
                'start' => 5361,
                'end' => 5372,
              ),
              'kind' => 'InputValueDefinition',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5361,
                  'end' => 5367,
                ),
                'kind' => 'Name',
                'value' => 'offset',
              ),
              'type' => 
              array (
                'loc' => 
                array (
                  'start' => 5369,
                  'end' => 5372,
                ),
                'kind' => 'NamedType',
                'name' => 
                array (
                  'loc' => 
                  array (
                    'start' => 5369,
                    'end' => 5372,
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
              'start' => 5378,
              'end' => 5389,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5379,
                'end' => 5388,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5379,
                  'end' => 5388,
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
        8 => 
        array (
          'loc' => 
          array (
            'start' => 5392,
            'end' => 5407,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5392,
              'end' => 5399,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5401,
              'end' => 5407,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5401,
                'end' => 5407,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5410,
            'end' => 5427,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5410,
              'end' => 5419,
            ),
            'kind' => 'Name',
            'value' => 'thumbnail',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5421,
              'end' => 5427,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5421,
                'end' => 5427,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5430,
            'end' => 5440,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5430,
              'end' => 5434,
            ),
            'kind' => 'Name',
            'value' => 'note',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5436,
              'end' => 5440,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5436,
                'end' => 5440,
              ),
              'kind' => 'Name',
              'value' => 'Note',
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
        'start' => 5444,
        'end' => 5854,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5449,
          'end' => 5458,
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
            'start' => 5470,
            'end' => 5474,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5470,
              'end' => 5474,
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
            'start' => 5479,
            'end' => 5486,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5479,
              'end' => 5481,
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
              'start' => 5483,
              'end' => 5486,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5483,
                'end' => 5485,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5483,
                  'end' => 5485,
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
            'start' => 5489,
            'end' => 5503,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5489,
              'end' => 5494,
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
              'start' => 5496,
              'end' => 5503,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5496,
                'end' => 5502,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5496,
                  'end' => 5502,
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
            'start' => 5506,
            'end' => 5521,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5506,
              'end' => 5513,
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
              'start' => 5515,
              'end' => 5521,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5515,
                'end' => 5521,
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
            'start' => 5524,
            'end' => 5543,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5524,
              'end' => 5535,
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
              'start' => 5537,
              'end' => 5543,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5537,
                'end' => 5543,
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
            'start' => 5546,
            'end' => 5563,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5546,
              'end' => 5555,
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
              'start' => 5557,
              'end' => 5563,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5558,
                'end' => 5562,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5558,
                  'end' => 5562,
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
            'start' => 5566,
            'end' => 5583,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5566,
              'end' => 5575,
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
              'start' => 5577,
              'end' => 5583,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5577,
                'end' => 5583,
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
            'start' => 5586,
            'end' => 5609,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5586,
              'end' => 5601,
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
              'start' => 5603,
              'end' => 5609,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5603,
                'end' => 5609,
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
            'start' => 5612,
            'end' => 5628,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5612,
              'end' => 5619,
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
              'start' => 5621,
              'end' => 5628,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5622,
                'end' => 5627,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5622,
                  'end' => 5627,
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
        8 => 
        array (
          'loc' => 
          array (
            'start' => 5631,
            'end' => 5653,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5631,
              'end' => 5644,
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
              'start' => 5646,
              'end' => 5653,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5647,
                'end' => 5652,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5647,
                  'end' => 5652,
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
            'start' => 5656,
            'end' => 5669,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5656,
              'end' => 5661,
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
              'start' => 5663,
              'end' => 5669,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5664,
                'end' => 5668,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5664,
                  'end' => 5668,
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
        10 => 
        array (
          'loc' => 
          array (
            'start' => 5672,
            'end' => 5688,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5672,
              'end' => 5679,
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
              'start' => 5681,
              'end' => 5688,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5682,
                'end' => 5687,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5682,
                  'end' => 5687,
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
            'start' => 5691,
            'end' => 5711,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5691,
              'end' => 5702,
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
              'start' => 5704,
              'end' => 5711,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5705,
                'end' => 5710,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5705,
                  'end' => 5710,
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
            'start' => 5714,
            'end' => 5731,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5714,
              'end' => 5723,
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
              'start' => 5725,
              'end' => 5731,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5725,
                'end' => 5731,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5734,
            'end' => 5750,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5734,
              'end' => 5743,
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
              'start' => 5745,
              'end' => 5750,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5745,
                'end' => 5750,
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
            'start' => 5753,
            'end' => 5767,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5753,
              'end' => 5759,
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
              'start' => 5761,
              'end' => 5767,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5761,
                'end' => 5767,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5770,
            'end' => 5782,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5770,
              'end' => 5775,
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
              'start' => 5777,
              'end' => 5782,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5777,
                'end' => 5782,
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
            'start' => 5785,
            'end' => 5798,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5785,
              'end' => 5789,
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
              'start' => 5791,
              'end' => 5798,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5792,
                'end' => 5797,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5792,
                  'end' => 5797,
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
            'start' => 5801,
            'end' => 5816,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5801,
              'end' => 5808,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5810,
              'end' => 5816,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5810,
                'end' => 5816,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5819,
            'end' => 5836,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5819,
              'end' => 5828,
            ),
            'kind' => 'Name',
            'value' => 'thumbnail',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5830,
              'end' => 5836,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5830,
                'end' => 5836,
              ),
              'kind' => 'Name',
              'value' => 'String',
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
            'start' => 5839,
            'end' => 5852,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5839,
              'end' => 5844,
            ),
            'kind' => 'Name',
            'value' => 'cover',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 5846,
              'end' => 5852,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5846,
                'end' => 5852,
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
    5 => 
    array (
      'loc' => 
      array (
        'start' => 5856,
        'end' => 5931,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5861,
          'end' => 5865,
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
            'start' => 5870,
            'end' => 5883,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5870,
              'end' => 5874,
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
              'start' => 5876,
              'end' => 5883,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5876,
                'end' => 5882,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5876,
                  'end' => 5882,
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
            'start' => 5886,
            'end' => 5899,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5886,
              'end' => 5890,
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
              'start' => 5892,
              'end' => 5899,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5892,
                'end' => 5898,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5892,
                  'end' => 5898,
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
            'start' => 5902,
            'end' => 5913,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5902,
              'end' => 5905,
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
              'start' => 5907,
              'end' => 5913,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5907,
                'end' => 5913,
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
            'start' => 5916,
            'end' => 5929,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5916,
              'end' => 5921,
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
              'start' => 5923,
              'end' => 5929,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5923,
                'end' => 5929,
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
        'start' => 5933,
        'end' => 6068,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 5938,
          'end' => 5942,
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
            'start' => 5954,
            'end' => 5958,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5954,
              'end' => 5958,
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
            'start' => 5963,
            'end' => 5970,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5963,
              'end' => 5965,
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
              'start' => 5967,
              'end' => 5970,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 5967,
                'end' => 5969,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 5967,
                  'end' => 5969,
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
            'start' => 5973,
            'end' => 5988,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5973,
              'end' => 5977,
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
              'start' => 5979,
              'end' => 5988,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5979,
                'end' => 5988,
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
            'start' => 5991,
            'end' => 6005,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 5991,
              'end' => 5997,
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
              'start' => 5999,
              'end' => 6005,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 5999,
                'end' => 6005,
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
            'start' => 6008,
            'end' => 6020,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6008,
              'end' => 6012,
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
              'start' => 6014,
              'end' => 6020,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6014,
                'end' => 6020,
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
            'start' => 6023,
            'end' => 6032,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6023,
              'end' => 6027,
            ),
            'kind' => 'Name',
            'value' => 'size',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6029,
              'end' => 6032,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6029,
                'end' => 6032,
              ),
              'kind' => 'Name',
              'value' => 'Int',
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
            'start' => 6035,
            'end' => 6048,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6035,
              'end' => 6040,
            ),
            'kind' => 'Name',
            'value' => 'mtime',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6042,
              'end' => 6048,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6042,
                'end' => 6048,
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
            'start' => 6051,
            'end' => 6066,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6051,
              'end' => 6058,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6060,
              'end' => 6066,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6060,
                'end' => 6066,
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
    7 => 
    array (
      'loc' => 
      array (
        'start' => 6070,
        'end' => 6225,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6075,
          'end' => 6079,
        ),
        'kind' => 'Name',
        'value' => 'Note',
      ),
      'interfaces' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6091,
            'end' => 6095,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6091,
              'end' => 6095,
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
            'start' => 6100,
            'end' => 6107,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6100,
              'end' => 6102,
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
              'start' => 6104,
              'end' => 6107,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6104,
                'end' => 6106,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6104,
                  'end' => 6106,
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
            'start' => 6110,
            'end' => 6119,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6110,
              'end' => 6114,
            ),
            'kind' => 'Name',
            'value' => 'item',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6116,
              'end' => 6119,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6116,
                'end' => 6118,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6116,
                  'end' => 6118,
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
        2 => 
        array (
          'loc' => 
          array (
            'start' => 6122,
            'end' => 6135,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6122,
              'end' => 6126,
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
              'start' => 6128,
              'end' => 6135,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6128,
                'end' => 6134,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6128,
                  'end' => 6134,
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
        3 => 
        array (
          'loc' => 
          array (
            'start' => 6138,
            'end' => 6153,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6138,
              'end' => 6145,
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
              'start' => 6147,
              'end' => 6153,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6147,
                'end' => 6153,
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
            'start' => 6156,
            'end' => 6165,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6156,
              'end' => 6160,
            ),
            'kind' => 'Name',
            'value' => 'size',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6162,
              'end' => 6165,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6162,
                'end' => 6165,
              ),
              'kind' => 'Name',
              'value' => 'Int',
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
            'start' => 6168,
            'end' => 6181,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6168,
              'end' => 6173,
            ),
            'kind' => 'Name',
            'value' => 'mtime',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6175,
              'end' => 6181,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6175,
                'end' => 6181,
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
            'start' => 6184,
            'end' => 6199,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6184,
              'end' => 6191,
            ),
            'kind' => 'Name',
            'value' => 'navlink',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6193,
              'end' => 6199,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6193,
                'end' => 6199,
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
            'start' => 6202,
            'end' => 6223,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6202,
              'end' => 6211,
            ),
            'kind' => 'Name',
            'value' => 'resources',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6213,
              'end' => 6223,
            ),
            'kind' => 'ListType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6214,
                'end' => 6222,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6214,
                  'end' => 6222,
                ),
                'kind' => 'Name',
                'value' => 'Resource',
              ),
            ),
          ),
          'directives' => 
          array (
          ),
        ),
      ),
    ),
    8 => 
    array (
      'loc' => 
      array (
        'start' => 6227,
        'end' => 6271,
      ),
      'kind' => 'ObjectTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6232,
          'end' => 6240,
        ),
        'kind' => 'Name',
        'value' => 'Resource',
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
            'start' => 6245,
            'end' => 6254,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6245,
              'end' => 6249,
            ),
            'kind' => 'Name',
            'value' => 'hash',
          ),
          'arguments' => 
          array (
          ),
          'type' => 
          array (
            'loc' => 
            array (
              'start' => 6251,
              'end' => 6254,
            ),
            'kind' => 'NonNullType',
            'type' => 
            array (
              'loc' => 
              array (
                'start' => 6251,
                'end' => 6253,
              ),
              'kind' => 'NamedType',
              'name' => 
              array (
                'loc' => 
                array (
                  'start' => 6251,
                  'end' => 6253,
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
            'start' => 6257,
            'end' => 6269,
          ),
          'kind' => 'FieldDefinition',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6257,
              'end' => 6261,
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
              'start' => 6263,
              'end' => 6269,
            ),
            'kind' => 'NamedType',
            'name' => 
            array (
              'loc' => 
              array (
                'start' => 6263,
                'end' => 6269,
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
    9 => 
    array (
      'loc' => 
      array (
        'start' => 6273,
        'end' => 6311,
      ),
      'kind' => 'UnionTypeDefinition',
      'name' => 
      array (
        'loc' => 
        array (
          'start' => 6279,
          'end' => 6291,
        ),
        'kind' => 'Name',
        'value' => 'SearchResult',
      ),
      'directives' => 
      array (
      ),
      'types' => 
      array (
        0 => 
        array (
          'loc' => 
          array (
            'start' => 6294,
            'end' => 6299,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6294,
              'end' => 6299,
            ),
            'kind' => 'Name',
            'value' => 'Entry',
          ),
        ),
        1 => 
        array (
          'loc' => 
          array (
            'start' => 6302,
            'end' => 6311,
          ),
          'kind' => 'NamedType',
          'name' => 
          array (
            'loc' => 
            array (
              'start' => 6302,
              'end' => 6311,
            ),
            'kind' => 'Name',
            'value' => 'EntryBook',
          ),
        ),
      ),
    ),
  ),
);
